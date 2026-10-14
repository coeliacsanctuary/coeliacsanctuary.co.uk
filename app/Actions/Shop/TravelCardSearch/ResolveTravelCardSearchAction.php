<?php

declare(strict_types=1);

namespace App\Actions\Shop\TravelCardSearch;

use App\Concerns\Shop\FormatsDigitalCard;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\TravelCardSearchTerm;
use App\Resources\Shop\ShopDigitalCardResource;
use App\Resources\Shop\ShopProductIndexResource;
use App\Support\Helpers;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;

class ResolveTravelCardSearchAction
{
    use FormatsDigitalCard;

    public function __construct(protected MatchTravelCardSearchTermsAction $matchTravelCardSearchTerms)
    {
    }

    /**
     * @return array{
     *     term: string,
     *     destinations: Collection<int, array{term: string, type: string, flag: string|null, products: AnonymousResourceCollection, digital_cards: AnonymousResourceCollection}>,
     *     covers_all: AnonymousResourceCollection
     * }|null
     */
    public function handle(string $searchString, ?ShopOrder $basket = null): ?array
    {
        $searchString = mb_trim($searchString);

        $terms = $this->matchTravelCardSearchTerms->handle($searchString);

        if ($terms->isEmpty()) {
            return null;
        }

        $terms->each(fn (TravelCardSearchTerm $term) => $term->increment('hits'));

        $this->loadProducts($terms);

        return [
            'term' => $searchString,
            'destinations' => $terms->map(fn (TravelCardSearchTerm $term) => [
                'term' => $term->display_term,
                'type' => $term->type,
                'flag' => Helpers::countryCode($term->term),
                'products' => ShopProductIndexResource::collection($this->products($term)),
                'digital_cards' => ShopDigitalCardResource::collection($this->digitalCards($term, $basket)),
            ])->values(),
            'covers_all' => ShopProductIndexResource::collection($this->coversAll($terms)),
        ];
    }

    /** @param EloquentCollection<int, TravelCardSearchTerm> $terms */
    protected function loadProducts(EloquentCollection $terms): void
    {
        $terms->load([
            'products' => fn (Relation $query) => $query
                ->with(['reviews', 'prices', 'variants', 'media'])
                ->orderByDesc('shop_products.pinned')
                ->orderBy('shop_products.title'),
            'digitalCards' => fn (Relation $query) => $query
                ->with(['price', 'product.variants', 'product.prices'])
                ->orderBy('shop_digital_cards.title'),
        ]);
    }

    /** @return EloquentCollection<int, ShopProduct> */
    protected function products(TravelCardSearchTerm $term): EloquentCollection
    {
        /** @var EloquentCollection<int, ShopProduct> $products */
        $products = $term->products
            /** @phpstan-ignore-next-line */
            ->filter(fn (ShopProduct $product) => $product->currentPrice !== null)
            ->values();

        return $products;
    }

    /** @return EloquentCollection<int, ShopDigitalCard> */
    protected function digitalCards(TravelCardSearchTerm $term, ?ShopOrder $basket): EloquentCollection
    {
        /** @var EloquentCollection<int, ShopDigitalCard> $digitalCards */
        $digitalCards = $term->digitalCards
            /** @phpstan-ignore-next-line */
            ->filter(fn (ShopDigitalCard $digitalCard) => $digitalCard->pivot->card_language === $digitalCard->language)
            ->map(fn (ShopDigitalCard $digitalCard) => $this->formatDigitalCard($digitalCard, $basket))
            ->values();

        return $digitalCards;
    }

    /**
     * @param  EloquentCollection<int, TravelCardSearchTerm> $terms
     * @return EloquentCollection<int, ShopProduct>
     */
    protected function coversAll(EloquentCollection $terms): EloquentCollection
    {
        $first = $terms->first();

        if ($terms->count() < 2 || ! $first instanceof TravelCardSearchTerm) {
            return new EloquentCollection();
        }

        /** @var array<int, int> $shared */
        $shared = $terms
            ->map(fn (TravelCardSearchTerm $term) => $this->products($term)->pluck('id')->all())
            ->reduce(
                fn (?array $carry, array $ids) => $carry === null ? $ids : array_intersect($carry, $ids),
                null,
            ) ?? [];

        /** @var EloquentCollection<int, ShopProduct> $products */
        $products = $this->products($first)->whereIn('id', $shared)->values();

        return $products;
    }
}
