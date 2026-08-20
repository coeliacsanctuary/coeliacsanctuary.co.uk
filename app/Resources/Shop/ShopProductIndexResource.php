<?php

declare(strict_types=1);

namespace App\Resources\Shop;

use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use App\Models\Shop\TravelCardSearchTerm;
use App\Support\Helpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;
use Money\Money;

/** @mixin ShopProduct */
class ShopProductIndexResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'link' => $this->link,
            'image' => $this->main_image_as_webp ?? $this->main_image,
            'price' => Helpers::formatMoney(Money::GBP($this->currentPrice)),
            'rating' => $this->whenLoaded('reviews', fn () => [
                'average' => $this->averageRating,
                'count' => $this->reviews->count(),
            ]),
            'in_stock' => $this->variants->contains(fn (ShopProductVariant $variant) => $variant->quantity > 0),
            'number_of_variants' => $this->variants->count(),
            'primary_variant' => $this->variants->first()?->id,
            'primary_variant_quantity' => $this->variants->first()?->quantity,
            'footnote' => $this->generateFootnote(),
        ];
    }

    protected function generateFootnote(): ?string
    {
        if ($this->isTravelCardProduct()) {
            return $this->travelCardCoverageLine();
        }

        return null;
    }

    protected function isTravelCardProduct(): bool
    {
        return $this->relationLoaded('categories')
            && Helpers::isTravelCard($this->categories->first());
    }

    protected function travelCardCoverageLine(): ?string
    {
        if ( ! $this->relationLoaded('travelCardSearchTerms')) {
            return null;
        }

        $countries = $this->travelCardSearchTerms
            ->filter(fn (TravelCardSearchTerm $term) => $term->pivot->card_show_on_product_page) /** @phpstan-ignore-line */
            ->sortBy([
                /** @phpstan-ignore-next-line */
                fn (TravelCardSearchTerm $a, TravelCardSearchTerm $b) => $b->pivot->card_score <=> $a->pivot->card_score,
                fn (TravelCardSearchTerm $a, TravelCardSearchTerm $b) => Str::lower($a->term) <=> Str::lower($b->term),
            ])
            ->map(fn (TravelCardSearchTerm $term) => Str::title($term->term))
            ->unique()
            ->values();

        if ($countries->isEmpty()) {
            return null;
        }

        $names = $countries->take(3);

        if ($countries->count() <= $names->count()) {
            return 'Covers ' . $names->join(', ', ' and ');
        }

        return "Covers {$countries->count()} countries including " . $names->join(', ', ' and ');
    }
}
