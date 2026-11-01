<?php

declare(strict_types=1);

namespace App\Listeners\Shop;

use App\Events\Shop\OrderCancelledEvent;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrder;
use App\Notifications\Shop\OrderCancelledNotification;

class SendOrderCancellationNotification
{
    public function handle(OrderCancelledEvent $event): void
    {
        $order = $event->order;

        /** @var ShopCustomer $customer */
        $customer = $order->customer;

        $customer->notify(new OrderCancelledNotification($order));
    }
}
