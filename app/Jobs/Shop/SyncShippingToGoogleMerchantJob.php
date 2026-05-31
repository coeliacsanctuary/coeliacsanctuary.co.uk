<?php

declare(strict_types=1);

namespace App\Jobs\Shop;

use App\Actions\Shop\SyncShippingToGoogleMerchantAction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncShippingToGoogleMerchantJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function handle(SyncShippingToGoogleMerchantAction $action): void
    {
        $action->handle();
    }
}
