<?php

declare(strict_types=1);

namespace Tests\Unit\Listeners\Shop;

use App\Events\Shop\OrderPaidEvent;
use App\Jobs\Shop\SyncProductToGoogleMerchantJob;
use App\Listeners\Shop\SyncProductsToGoogleMerchantOnOrderPaid;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use Database\Seeders\ShopScaffoldingSeeder;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SyncProductsToGoogleMerchantOnOrderPaidTest extends TestCase
{
    protected ShopOrder $order;

    protected ShopProduct $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ShopScaffoldingSeeder::class);
        $this->withCategoriesAndProducts(categories: 1, products: 1, variants: 1);

        $this->product = ShopProduct::query()->first();

        $this->order = $this->build(ShopOrder::class)->asPaid()->create();

        $this->build(ShopOrderItem::class)
            ->inOrder($this->order)
            ->add($this->product->variants->first())
            ->create();

        Bus::fake();
    }

    #[Test]
    public function itDoesNothingWhenGoogleMerchantIsDisabled(): void
    {
        config()->set('google-merchant.enabled', false);

        $event = new OrderPaidEvent($this->order);

        app(SyncProductsToGoogleMerchantOnOrderPaid::class)->handle($event);

        Bus::assertNothingDispatched();
    }

    #[Test]
    public function itDispatchesASyncJobForEachProductInTheOrder(): void
    {
        config()->set('google-merchant.enabled', true);

        $event = new OrderPaidEvent($this->order);

        app(SyncProductsToGoogleMerchantOnOrderPaid::class)->handle($event);

        Bus::assertDispatchedTimes(SyncProductToGoogleMerchantJob::class, 1);
    }

}
