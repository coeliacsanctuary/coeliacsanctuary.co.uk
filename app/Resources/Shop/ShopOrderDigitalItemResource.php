<?php

declare(strict_types=1);

namespace App\Resources\Shop;

use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopOrderDigitalItem;
use App\Models\Shop\TravelCardSearchTerm;
use App\Support\Helpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Money\Money;

/** @mixin ShopOrderDigitalItem */
class ShopOrderDigitalItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var ShopDigitalCard $card */
        $card = $this->card;

        /** @var ?TravelCardSearchTerm $country */
        $country = $card->countries?->first();

        return [
            'id' => $this->id,
            'title' => $this->digital_card_title,
            'item_price' => Helpers::formatMoney(Money::GBP($this->digital_card_price)),
            'hasBundleInBasket' => $this->physical_order_item_id !== null,
            'country' => $country ? [
                'label' => ucwords($country->term),
                'code' => Helpers::countryCode($country->term),
            ] : null,
        ];
    }
}
