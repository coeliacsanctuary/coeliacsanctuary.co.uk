<?php

declare(strict_types=1);

namespace App\Resources\Shop;

use App\Models\Shop\ShopProduct;
use App\Support\Helpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
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
            'image' => $this->main_image,
            'price' => Helpers::formatMoney(Money::GBP($this->currentPrice)),
            'rating' => $this->whenLoaded('reviews', [
                'average' => $this->averageRating,
                'count' => $this->reviews->count(),
            ]),
            'number_of_variants' => $this->variants->count(),
            'primary_variant' => $this->variants->first()?->id,
        ];
    }
}
