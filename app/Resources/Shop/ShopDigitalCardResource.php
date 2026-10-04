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
        $prices = $this->getPrices();

        return [
            'id' => $this->id,
            'type' => $this->type->label(),
            'title' => $this->title,
            'slug' => $this->slug,
            'language' => $this->language,
            'native_language' => $this->native_language,
            'product' => $this->whenLoaded('product', fn (ShopProduct $product) => [
                'title' => $product->title,
                'link' => $product->link,
                'id' => $product->id,
                'variantId' => $product->variants->first()?->id,
                'price' => $prices['physical'],
                'isInBasket' => $this->hasAttribute('physicalProductInBasket') && $this->getAttribute('physicalProductInBasket'),
                'quantity' => $product->variants->first()?->quantity,
            ]),
            'countries' => $this->whenLoaded('countries', fn (Collection $countries) => $countries->map(fn (TravelCardSearchTerm $searchTerm) => [
                'country' => ucwords($searchTerm->term),
                'code' => Helpers::countryCode($searchTerm->term),
            ])),
            'price' => [
                'default' => $prices['default'],
                'extra' => $prices['extra'],
                'bundle' => $prices['bundle'],
                'saving' => $prices['saving'],
            ],
            'flag' => $this->whenLoaded('countries', fn (Collection $countries) => Helpers::countryCode($countries->first()->term)),
        ];
    }

    /** @return array{default: string, extra: string, physical: string|null, bundle: string|null, saving: string|null} */
    protected function getPrices(): array
    {
        /** @var ShopDigitalCardPrice $price */
        $price = $this->price ?? ShopDigitalCard::defaultPrice();

        /** @var ?ShopProduct $product */
        $product = $this->product;

        $cardPrice = $price->price;
        $cardPriceWithBundle = $price->price_with_physical_product;
        $productPrice = $product?->currentPrice;

        $totalPrice = $productPrice + $cardPriceWithBundle;

        return [
            'default' => Helpers::formatMoney(Money::GBP($cardPrice)),
            'extra' => Helpers::formatMoney(Money::GBP($cardPriceWithBundle)),
            'physical' => $product && $productPrice ? Helpers::formatMoney(Money::GBP($productPrice)) : null,
            'bundle' => $product ? Helpers::formatMoney(Money::GBP($totalPrice)) : null,
            'saving' => $product ? Helpers::formatMoney(Money::GBP(($productPrice + $cardPrice) - $totalPrice)) : null,
        ];
    }
}
