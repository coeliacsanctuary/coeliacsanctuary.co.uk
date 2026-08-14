<?php

declare(strict_types=1);

namespace App\Listeners\Shop;

use App\Events\Shop\OrderPaidEvent;
use App\Jobs\Shop\SyncProductToGoogleMerchantJob;
use App\Models\Shop\ShopOrderItem;

class SyncProductsToGoogleMerchantOnOrderPaid
{
    public function handle(OrderPaidEvent $event): void
    {
        if ( ! config('google-merchant.enabled')) {
            return;
        }

        $event->order->items->each(function (ShopOrderItem $item): void {
            SyncProductToGoogleMerchantJob::dispatch($item->product);
        });
    }
}
