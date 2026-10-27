<?php

declare(strict_types=1);

namespace App\Listeners\Shop;

use App\Events\Shop\OrderPaidEvent;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrder;
use App\Notifications\Shop\NewOrderAlertNotification;
use App\Notifications\Shop\OrderConfirmedNotification;
use App\Support\Helpers;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOrderConfirmationMails implements ShouldQueue
{
    public function handle(OrderPaidEvent $event): void
    {
        /** @var ShopOrder $order */
        $order = $event->order;

        /** @var ShopCustomer $customer */
        $customer = $order->customer;

        $customer->notify(new OrderConfirmedNotification($order));
        Helpers::adminUser()->notify(new NewOrderAlertNotification($order));
    }
}
