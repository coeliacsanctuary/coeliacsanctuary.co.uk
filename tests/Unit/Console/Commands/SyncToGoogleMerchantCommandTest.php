<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\SyncToGoogleMerchantCommand;
use App\Jobs\Shop\SyncProductToGoogleMerchantJob;
use App\Jobs\Shop\SyncShippingToGoogleMerchantJob;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use Database\Seeders\ShopScaffoldingSeeder;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Config;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SyncToGoogleMerchantCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('google-merchant.enabled', true);

        $this->seed(ShopScaffoldingSeeder::class);
        $this->withCategoriesAndProducts(categories: 1, products: 2, variants: 1);

        Bus::fake();
    }

    #[Test]
    public function itWarnsAndDoesNothingWhenGoogleMerchantIsDisabled(): void
    {
        config()->set('google-merchant.enabled', false);

        $this->artisan(SyncToGoogleMerchantCommand::class)
            ->expectsOutputToContain('Google Merchant is disabled')
            ->run();

        Bus::assertNothingDispatched();
    }

    #[Test]
    public function itDispatchesProductAndShippingJobsWhenAllIsSelected(): void
    {
        $this->artisan(SyncToGoogleMerchantCommand::class)
            ->expectsChoice('What would you like to sync?', 'all', [
                'all' => 'Products and shipping (recommended)',
                'products' => 'Products only',
                'shipping' => 'Shipping settings only',
            ])
            ->run();

        Bus::assertDispatchedTimes(SyncProductToGoogleMerchantJob::class, 2);
        Bus::assertDispatchedTimes(SyncShippingToGoogleMerchantJob::class, 1);
    }

    #[Test]
    public function itOnlyDispatchesProductJobsWhenProductsIsSelected(): void
    {
        $this->artisan(SyncToGoogleMerchantCommand::class)
            ->expectsChoice('What would you like to sync?', 'products', [
                'all' => 'Products and shipping (recommended)',
                'products' => 'Products only',
                'shipping' => 'Shipping settings only',
            ])
            ->run();

        Bus::assertDispatchedTimes(SyncProductToGoogleMerchantJob::class, 2);
        Bus::assertNotDispatched(SyncShippingToGoogleMerchantJob::class);
    }

    #[Test]
    public function itOnlyDispatchesShippingJobWhenShippingIsSelected(): void
    {
        $this->artisan(SyncToGoogleMerchantCommand::class)
            ->expectsChoice('What would you like to sync?', 'shipping', [
                'all' => 'Products and shipping (recommended)',
                'products' => 'Products only',
                'shipping' => 'Shipping settings only',
            ])
            ->run();

        Bus::assertDispatchedTimes(SyncShippingToGoogleMerchantJob::class, 1);
        Bus::assertNotDispatched(SyncProductToGoogleMerchantJob::class);
    }

    #[Test]
    public function itOnlyDispatchesProductJobsForProductsWithLiveVariants(): void
    {
        $product = $this->build(ShopProduct::class)->create();

        $this->build(ShopProductVariant::class)
            ->notLive()
            ->belongsToProduct($product)
            ->create();

        $this->artisan(SyncToGoogleMerchantCommand::class)
            ->expectsChoice('What would you like to sync?', 'products', [
                'all' => 'Products and shipping (recommended)',
                'products' => 'Products only',
                'shipping' => 'Shipping settings only',
            ])
            ->run();

        Bus::assertDispatchedTimes(SyncProductToGoogleMerchantJob::class, 2);
    }
}
