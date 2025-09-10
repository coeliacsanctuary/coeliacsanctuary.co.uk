<?php

declare(strict_types=1);

namespace App\Nova\Actions\Shop;

use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrder;
use App\Notifications\Shop\DownloadYourProductsNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

class ResendDownloadLink extends Action
{
    use InteractsWithQueue;
    use Queueable;

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models): void
    {
        /** @var ShopOrder $order */
        $order = $models->first();

        $downloadLink = $order->downloadLinks()->create([
            'expires_at' => now()->addMonth(),
        ]);

        /** @var ShopCustomer $customer */
        $customer = $order->customer;

        $customer->notifyNow(new DownloadYourProductsNotification($downloadLink));
    }
}
