<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDigitalItem;
use App\Models\Shop\ShopOrderItem;

class ReconcileBasketItemsAction
{
    public function handle(ShopOrder $basket): void
    {
        $basket->loadMissing(['items', 'digitalItems', 'digitalItems.card', 'digitalItems.card.price', 'digitalItems.card.product']);

        $basket->digitalItems->each(function (ShopOrderDigitalItem $item) use ($basket): void {
            if ( ! $item->card->product) {
                return;
            }

            $price = $item->card->price ?? ShopDigitalCard::defaultPrice();

            if ($basket->items->where('product_id', $item->card->product_id)->isEmpty()) {
                if ($item->digital_card_price !== $price->price) {
                    $item->digital_card_price = $price->price;
                }

                if ($item->physical_order_item_id) {
                    $item->orderItem()->dissociate();
                }

                $item->saveQuietly();

                return;
            }

            if ($item->digital_card_price !== $price->price_with_physical_product) {
                $item->digital_card_price = $price->price_with_physical_product;
            }

            /** @var ShopOrderItem $physicalItem */
            $physicalItem = $basket->items->where('product_id', $item->card->product_id)->first();

            if ($item->physical_order_item_id !== $physicalItem->id) {
                $item->orderItem()->associate($physicalItem);
            }

            $item->saveQuietly();
        });

        $basket->updateQuietly([
            'has_digital_items' => $basket->digitalItems->isNotEmpty(),
            'is_digital_only' => $basket->items->isEmpty(),
        ]);
    }
}
