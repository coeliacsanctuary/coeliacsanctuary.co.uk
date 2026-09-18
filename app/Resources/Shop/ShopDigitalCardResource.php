<?php

declare(strict_types=1);

namespace App\Resources\Shop;

use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopDigitalCardPrice;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\TravelCardSearchTerm;
use App\Support\Helpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;
use Money\Money;

/** @mixin ShopDigitalCard */
class ShopDigitalCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var ShopDigitalCardPrice $price */
        $price = $this->price ?? ShopDigitalCard::defaultPrice();

        return [
            'type' => $this->type->label(),
            'title' => $this->title,
            'slug' => $this->slug,
            'language' => $this->language,
            'native_language' => $this->native_language,
            'product' => $this->whenLoaded('product', fn (ShopProduct $product) => [
                'title' => $product->title,
                'link' => $product->link,
            ]),
            'countries' => $this->whenLoaded('countries', fn (Collection $countries) => $countries->map(fn (TravelCardSearchTerm $searchTerm) => [
                'country' => ucwords($searchTerm->term),
                'code' => Helpers::countryCode($searchTerm->term),
            ])),
            'price' => Helpers::formatMoney(Money::GBP($price->price)),
            'flag' => $this->whenLoaded('countries', fn (Collection $countries) => Helpers::countryCode($countries->first()->term)),
        ];
    }
}
