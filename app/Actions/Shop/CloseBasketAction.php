<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProductVariant;
use RuntimeException;

class CloseBasketAction
{
    public function handle(ShopOrder $order): void
    {
        if ( ! in_array($order->state_id, [OrderState::BASKET, OrderState::PENDING], true)) {
            throw new RuntimeException('Order must be in basket or pending state');
        }

        $order->update(['state_id' => OrderState::EXPIRED]);

        $order->items->each(function (ShopOrderItem $item): void {
            /** @var ShopProductVariant $variant */
            $variant = $item->variant;

            $variant->increment('quantity', $item->quantity);
        });
    }
}
