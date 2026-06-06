<?php

declare(strict_types=1);

namespace App\Jobs\Shop;

use App\Actions\Shop\SyncProductToGoogleMerchantAction;
use App\Models\Shop\ShopProduct;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncProductToGoogleMerchantJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(protected ShopProduct $product)
    {
    }

    public function uniqueId(): int
    {
        return $this->product->id;
    }

    public function handle(SyncProductToGoogleMerchantAction $action): void
    {
        $action->handle($this->product);
    }
}
