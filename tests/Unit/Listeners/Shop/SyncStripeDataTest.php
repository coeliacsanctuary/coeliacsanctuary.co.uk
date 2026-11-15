<?php

declare(strict_types=1);

namespace Tests\Unit\Listeners\Shop;

use App\Events\Shop\OrderPaidEvent;
use App\Listeners\Shop\SyncStripeData;
use App\Models\Shop\ShopOrder;
use Database\Seeders\ShopScaffoldingSeeder;
use PHPUnit\Framework\Attributes\Test;
use Stripe\Service\ChargeService;
use Tests\Concerns\MocksStripe;
use Tests\TestCase;

class SyncStripeDataTest extends TestCase
{
    use MocksStripe;

    protected ShopOrder $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ShopScaffoldingSeeder::class);

        $this->order = $this->build(ShopOrder::class)->asPaid()->create();
    }

    #[Test]
    public function itUpdatesTheStripeChargeWithTheOrderMetadata(): void
    {
        $charges = $this->partialMock(ChargeService::class);
        $this->getStripeClient()->charges = $charges;

        $charges->shouldReceive('update')
            ->withArgs(function (string $chargeId, array $params): true {
                $this->assertEquals($this->order->payment->response->charge_id, $chargeId);
                $this->assertEquals([
                    'metadata' => [
                        'order_key' => $this->order->order_key,
                        'order_id' => $this->order->id,
                        'customer_id' => $this->order->customer_id,
                        'customer_name' => $this->order->customer->name,
                    ],
                ], $params);

                return true;
            })
            ->once();

        app(SyncStripeData::class)->handle(new OrderPaidEvent($this->order));
    }

    #[Test]
    public function itDoesntUpdateStripeIfTheOrderHasNoPayment(): void
    {
        $this->order->payment->response->delete();
        $this->order->payment->delete();

        $this->expectNoChargeUpdate();

        app(SyncStripeData::class)->handle(new OrderPaidEvent($this->order->fresh()));
    }

    #[Test]
    public function itDoesntUpdateStripeIfThePaymentHasNoResponse(): void
    {
        $this->order->payment->response->delete();

        $this->expectNoChargeUpdate();

        app(SyncStripeData::class)->handle(new OrderPaidEvent($this->order->fresh()));
    }

    #[Test]
    public function itDoesntUpdateStripeIfThePaymentResponseHasNoChargeId(): void
    {
        $this->order->payment->response->update(['charge_id' => null]);

        $this->expectNoChargeUpdate();

        app(SyncStripeData::class)->handle(new OrderPaidEvent($this->order->fresh()));
    }

    protected function expectNoChargeUpdate(): void
    {
        $charges = $this->partialMock(ChargeService::class);
        $this->getStripeClient()->charges = $charges;

        $charges->shouldNotReceive('update');
    }
}
