<?php

declare(strict_types=1);

namespace App\Resources\Shop;

use App\Models\Shop\ShopOrderReviewItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ShopOrderReviewItem */
class ShopIndexReviewResource extends JsonResource
{
    /** @return array{name: string | null, review: string | null, rating: float, product: array{title: string, link: string} | null} */
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->parent?->name,
            'review' => $this->review,
            'rating' => $this->rating,
            'product' => $this->product ? [
                'title' => $this->product->title,
                'link' => $this->product->link,
            ] : null,
        ];
    }
}
