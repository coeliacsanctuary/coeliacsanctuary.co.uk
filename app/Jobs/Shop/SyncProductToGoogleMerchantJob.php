<?php

declare(strict_types=1);

namespace App\Jobs\Shop;

use App\Actions\Shop\SyncProductToGoogleMerchantAction;
use App\Models\Shop\ShopProduct;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncProductToGoogleMerchantJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected ShopProduct $product)
    {
    }

    public function handle(SyncProductToGoogleMerchantAction $action): void
    {
        $action->handle($this->product);
    }
}
