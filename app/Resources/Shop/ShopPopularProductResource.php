<?php

declare(strict_types=1);

namespace App\Resources\Shop;

use App\Models\Shop\ShopProduct;
use App\Support\Helpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Money\Money;

/** @mixin ShopProduct */
class ShopPopularProductResource extends JsonResource
{
    /** @return array{title: string, link: string, image: string, price: string, rating: array{average: float, count: int} | null} */
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->title,
            'link' => $this->link,
            'image' => $this->main_image_as_webp ?? $this->main_image,
            'price' => Helpers::formatMoney(Money::GBP($this->currentPrice)),
            'rating' => $this->whenLoaded('reviews', fn () => [
                'average' => $this->averageRating,
                'count' => $this->reviews->count(),
            ]),
        ];
    }
}
