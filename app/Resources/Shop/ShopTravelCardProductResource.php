<?php

declare(strict_types=1);

namespace App\Resources\Shop;

use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\TravelCardSearchTerm;
use App\Support\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Money\Money;

/** @mixin ShopProduct */
class ShopTravelCardProductResource extends ShopProductResource
{
    public function toArray(Request $request): array
    {
        $digitalDefaultPrice = ShopDigitalCard::defaultPrice();

        return array_merge([
            'is_travel_card' => true,
            'countries' => $this->whenLoaded(
                'travelCardSearchTerms',
                fn (): Collection => $this->getCountries(),
            ),
            'digital_cards' => config('coeliac.digital_cards_enabled') ? $this->whenLoaded('digitalCards', $this->digitalCards->map(fn (ShopDigitalCard $digitalCard): array => [
                'id' => $digitalCard->id,
                'name' => $digitalCard->title,
                'slug' => $digitalCard->slug,
                'price' => Helpers::formatMoney(Money::GBP($digitalCard->price->price_with_physical_product ?? $digitalDefaultPrice->price_with_physical_product)),
                'raw_price' => $digitalCard->price->price_with_physical_product ?? $digitalDefaultPrice->price_with_physical_product,
            ])) : [],
            'digital_cards_link' => route('shop.digital-standard-cards'),
        ], parent::toArray($request));
    }

    /** @return Collection<int, array{languages: string, countries: array{country: string, code: string}}> */
    protected function getCountries(): Collection
    {
        /** @var Collection<int, Collection<int, TravelCardSearchTerm>> $countries */
        $countries = $this
            ->travelCardSearchTerms
            ->filter(fn (TravelCardSearchTerm $term) => $term->pivot->card_show_on_product_page) /** @phpstan-ignore-line */
            ->groupBy('pivot.card_language')
            ->when(
                fn (Collection $collection) => $collection->has('both'),
                /** @param Collection<string, Collection<int, TravelCardSearchTerm>> $collection */
                function (Collection $collection): void {
                    $keys = Str::of($this->title)
                        ->before(' Coeliac Gluten Free Travel Translation Card')
                        ->explode(' and ')
                        ->toArray();

                    /** @var Collection<int, TravelCardSearchTerm> $both */
                    $both = $collection->get('both');

                    $both->each(function (TravelCardSearchTerm $term) use ($keys, $collection): void {
                        foreach ($keys as $key) {
                            $collection->has($key) ?: $collection->put($key, collect());

                            /** @var Collection<int, TravelCardSearchTerm> $currentItems */
                            $currentItems = $collection->get($key);

                            $collection->put($key, $currentItems->push($term));
                        }
                    });
                }
            )
            ->forget('both');

        /** @var Collection<int, array{languages: string, countries: array{country: string, code: string}}> $sortedCountries */
        /** @phpstan-ignore-next-line  */
        $sortedCountries = $countries->map(fn (Collection $countries, string $language): array => [
            'language' => $language,
            'countries' => $countries
                ->sortBy([
                    /** @phpstan-ignore-next-line */
                    fn (TravelCardSearchTerm $a, TravelCardSearchTerm $b): int => $b->pivot->card_score <=> $a->pivot->card_score,
                    fn (TravelCardSearchTerm $a, TravelCardSearchTerm $b): int => Str::lower($a->term) <=> Str::lower($b->term),
                ])
                ->map(fn (TravelCardSearchTerm $term): array => [
                    'country' => Str::title($term->term),
                    'code' => Helpers::countryCode($term->term),
                ])
                ->unique('country')
                ->values()
                ->toArray(),
        ]);

        return $sortedCountries->values();
    }
}
