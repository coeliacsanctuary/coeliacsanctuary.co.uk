<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs\Shop;

use App\Actions\Shop\SyncProductToGoogleMerchantAction;
use App\Jobs\Shop\SyncProductToGoogleMerchantJob;
use App\Models\Shop\ShopProduct;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SyncProductToGoogleMerchantJobTest extends TestCase
{
    #[Test]
    public function itCallsTheSyncAction(): void
    {
        $this->withCategoriesAndProducts(categories: 1, products: 1, variants: 1);

        $product = ShopProduct::query()->first();

        $this->mock(SyncProductToGoogleMerchantAction::class)
            ->shouldReceive('handle')
            ->with($product)
            ->once();

        (new SyncProductToGoogleMerchantJob($product))->handle(app(SyncProductToGoogleMerchantAction::class));
    }
}
