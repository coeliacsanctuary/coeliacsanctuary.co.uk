<?php

declare(strict_types=1);

namespace App\Resources\Shop;

use App\Models\Shop\ShopDigitalCard;
use App\Support\Helpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Money\Money;

/** @mixin ShopDigitalCard */
class ShopProductDigitalBundleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $price = $this->price ?? ShopDigitalCard::defaultPrice();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'item_price' => Helpers::formatMoney(Money::GBP($price->price_with_physical_product)),
            'isInBasket' => $this->getAttribute('isInBasket'),
            'digital_item_id' => $this->getAttribute('digital_item_id'),
        ];
    }
}
