<?php

declare(strict_types=1);

namespace App\Resources\Shop;

use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\TravelCardSearchTerm;
use App\Support\Helpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Money\Money;

/** @mixin ShopDigitalCard */
class ShopDigitalCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'type' => $this->type->label(),
            'title' => $this->title,
            'slug' => $this->slug,
            'language' => $this->language,
            'native_language' => $this->native_language,
            'product' => $this->whenLoaded('product', fn () => [
                'title' => $this->product->title,
                'link' => $this->product->link,
            ]),
            'countries' => $this->whenLoaded('countries', fn () => $this->countries->map(fn (TravelCardSearchTerm $searchTerm) => [
                'country' => ucwords($searchTerm->term),
                'code' => Helpers::countryCode($searchTerm->term),
            ])),
            'price' => Helpers::formatMoney(Money::GBP($this->price->price)),
            'flag' => $this->whenLoaded('countries', fn () => Helpers::countryCode($this->countries->first()->term)),
        ];
    }
}
