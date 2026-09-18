<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;

class AddDigitalCardToBasketAction
{
    public function handle(ShopOrder $order, ShopDigitalCard $card, ?ShopOrderItem $shopOrderItem): void
    {
        $price = $card->price ?? ShopDigitalCard::defaultPrice();

        $item = $order->digitalItems()->firstOrCreate([
            'digital_card_id' => $card->id,
        ], [
            'digital_card_title' => $card->title,
            'digital_card_price' => $price->price,
        ]);

        if ($shopOrderItem) {
            $item->update([
                'physical_order_item_id' => $shopOrderItem->id,
                'digital_card_price' => $price->price_with_physical_product,
            ]);
        }

        $order->touch();
    }
}
