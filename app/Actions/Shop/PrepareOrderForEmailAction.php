<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Concerns\Shop\FormatsDigitalCard;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDigitalItem;
use App\Models\Shop\ShopOrderItem;

class PrepareOrderForEmailAction
{
    use FormatsDigitalCard;

    public function handle(ShopOrder $order): ShopOrder
    {
        $order->loadMissing([
            'address', 'customer', 'items', 'items.product', 'items.variant', 'items.product.media', 'payment', 'refunds',
            'items.digitalBundles', 'items.digitalBundles.card', 'items.digitalBundles.card.price',
            'items.digitalBundles.card.product', 'items.digitalBundles.card.product.travelCardSearchTerms',
            'items.digitalBundles.orderItem',
            'digitalItems', 'digitalItems.card', 'digitalItems.card.price',
            'digitalItems.card.product', 'digitalItems.card.product.travelCardSearchTerms',
            'digitalItems.orderItem',
        ]);

        $order->items->each(function (ShopOrderItem $item) use ($order): void {
            $item->digitalBundles->each(function (ShopOrderDigitalItem $digitalItem) use ($order): void {
                $digitalItem->card = $this->formatDigitalCard($digitalItem->card, $order);
            });
        });

        $order->digitalItems->each(function (ShopOrderDigitalItem $item) use ($order): void {
            $item->card = $this->formatDigitalCard($item->card, $order);
        });

        return $order;
    }
}
