<?php

declare(strict_types=1);

namespace App\Resources\Shop;

use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopOrderReviewItem;
use App\Models\Shop\ShopProduct;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ShopOrderReviewItem */
class ShopIndexReviewResource extends JsonResource
{
    /** @return array{name: string | null, review: string | null, rating: float, product: array{title: string, link: string} | null} */
    public function toArray(Request $request): array
    {
        /** @var ShopProduct | ShopDigitalCard | null $product */
        $product = $this->product;

        return [
            'name' => $this->parent?->name,
            'review' => $this->review,
            'rating' => $this->rating,
            'product' => $product ? [
                'title' => $product->title,
                'link' => $product instanceof ShopProduct ? $product->link : $product->type_id->route(),
            ] : null,
        ];
    }
}
