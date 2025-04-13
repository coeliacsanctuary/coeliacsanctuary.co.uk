<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use App\Notifications\Shop\AbandonedBasketNotification;
use Illuminate\Console\Command;

class SendAbandonedBasketEmailCommand extends Command
{
    protected $signature = 'coeliac:send-abandoned-basket-email';

    public function handle(): void
    {
        /** @var int $limit */
        $limit = config('coeliac.shop.abandoned_basket_time_limit');

        ShopOrder::query()
            ->where('state_id', OrderState::EXPIRED)
            ->where('updated_at', '<', now()->subHours($limit))
            ->where('updated_at', '>', now()->startOfDay())
            ->where('sent_abandoned_basket_email', false)
            ->with('items', 'items.variant')
            ->withWhereHas('customer')
            ->get()
            ->each(function (ShopOrder $basket): void {
                $itemsInStock = $basket->items->reject(fn (ShopOrderItem $item) => $item->variant?->quantity === 0);

                if ($itemsInStock->isEmpty()) {
                    return;
                }

                $basket->customer?->notify(new AbandonedBasketNotification($basket));
                $basket->update(['sent_abandoned_basket_email' => true]);
            });
    }
}
