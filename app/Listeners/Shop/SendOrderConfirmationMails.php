<?php

declare(strict_types=1);

namespace App\Listeners\Shop;

use App\Events\Shop\OrderPaidEvent;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrder;
use App\Notifications\Shop\OrderConfirmedNotification;
use App\Support\Helpers;

class SendOrderConfirmationMails
{
    public function __construct()
    {
    }

    public function handle(OrderPaidEvent $event): void
    {
        /** @var ShopOrder $order */
        $order = $event->order;

        /** @var ShopCustomer $customer */
        $customer = $order->customer;

        $notification = new OrderConfirmedNotification($order);

        $customer->notify($notification);
        Helpers::adminUser()->notify($notification);
    }
}
