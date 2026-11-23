<?php

declare(strict_types=1);

namespace App\Jobs\Shop;

use Illuminate\Foundation\Queue\Queueable;
use App\Actions\Shop\SyncProductToGoogleMerchantAction;
use App\Models\Shop\ShopProduct;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;

class SyncProductToGoogleMerchantJob implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

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
