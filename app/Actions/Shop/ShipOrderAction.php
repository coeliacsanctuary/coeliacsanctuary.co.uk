<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Enums\Shop\OrderState;
use App\Events\Shop\OrderShippedEvent;
use App\Models\Shop\ShopOrder;
use RuntimeException;

class ShipOrderAction
{
    public function handle(ShopOrder $order): void
    {
        throw_if($order->state_id !== OrderState::READY, new RuntimeException('Order must be in ready state'));

        $order->update([
            'state_id' => OrderState::SHIPPED,
            'shipped_at' => now(),
        ]);

        OrderShippedEvent::dispatch($order);
    }
}
