<?php

declare(strict_types=1);

namespace App\Resources\Shop;

use App\Models\Media;
use App\Models\Shop\ShopOrderReviewItem;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

/** @mixin ShopProduct */
class ShopProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $variants = $this->variants->sortBy(function (ShopProductVariant $variant) {
            if ($variant->primary_variant) {
                return -1;
            }

            return $variant->id;
        });

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'long_description' => Str::markdown($this->long_description, [
                'renderer' => [
                    'soft_break' => '<br />',
                ],
            ]),
            'image' => $this->main_image,
            'additional_images' => $this->getMedia('additional')->map(fn (Media $media) => $media->getUrl()),
            'prices' => $this->primaryVariant()->price,
            'has_multiple_prices' => $this->hasMultiplePrices(),
            'rating' => $this->whenLoaded('reviews', [
                'average' => $this->averageRating,
                'count' => $this->reviews->count(),
                'breakdown' => collect(range(5, 1))->map(fn ($rating) => [
                    'rating' => $rating,
                    'count' => $this->reviews->filter(fn (ShopOrderReviewItem $reviewItem) => (int) $reviewItem->rating === $rating)->count(),
                ]),
            ]),
            'category' => $this->whenLoaded('categories', [
                'title' => $this->categories->first()?->title,
                'link' => $this->categories->first()?->link,
            ]),
            'variant_title' => $this->variant_title,
            'variants' => ShopProductVariantResource::collection($variants),
        ];
    }
}
