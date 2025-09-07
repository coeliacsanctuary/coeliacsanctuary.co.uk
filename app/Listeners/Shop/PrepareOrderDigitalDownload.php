<?php

declare(strict_types=1);

namespace App\Listeners\Shop;

use App\Events\Shop\OrderPaidEvent;
use App\Models\Shop\ShopOrder;
use App\Notifications\Shop\DownloadYourProductsNotification;

class PrepareOrderDigitalDownload
{
    public function handle(OrderPaidEvent $event): void
    {
        /** @var ShopOrder $order */
        $order = $event->order;

        if ( ! $order->has_digital_products) {
            return;
        }

        $downloadLink = $order->downloadLinks()->create([
            'expires_at' => now()->addMonth(),
        ]);

        $order->customer->notify(new DownloadYourProductsNotification($downloadLink));
    }
}
