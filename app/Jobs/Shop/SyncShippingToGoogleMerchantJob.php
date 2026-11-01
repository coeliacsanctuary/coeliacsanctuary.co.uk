<?php

declare(strict_types=1);

namespace App\Jobs\Shop;

use Illuminate\Foundation\Queue\Queueable;
use App\Actions\Shop\SyncShippingToGoogleMerchantAction;
use Illuminate\Contracts\Queue\ShouldQueue;

class SyncShippingToGoogleMerchantJob implements ShouldQueue
{
    use Queueable;

    public function handle(SyncShippingToGoogleMerchantAction $action): void
    {
        $action->handle();
    }
}
