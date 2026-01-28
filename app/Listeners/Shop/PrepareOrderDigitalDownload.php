<?php

declare(strict_types=1);

namespace App\Listeners\Shop;

use App\Events\Shop\OrderPaidEvent;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrder;
use App\Notifications\Shop\DownloadYourProductsNotification;

class PrepareOrderDigitalDownload
{
    public function handle(OrderPaidEvent $event): void
    {
        /** @var ShopOrder $order */
        $order = $event->order;

        if ( ! $order->has_add_ons) {
            return;
        }

        $downloadLink = $order->downloadLinks()->create([
            'expires_at' => now()->addMonth(),
        ]);

        /** @var ShopCustomer $customer */
        $customer = $order->customer;

        $customer->notifyNow(new DownloadYourProductsNotification($downloadLink));

        $order->update([
            'add_ons_sent_at' => now(),
        ]);
    }
}
