<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop;

use PHPUnit\Framework\Attributes\Test;
use App\Actions\Shop\MarkOrderAsPaidAction;
use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopPayment;
use RuntimeException;
use Tests\Concerns\MocksStripe;
use Tests\TestCase;

class MarkOrderAsPaidActionTest extends TestCase
{
    use MocksStripe;

    protected ShopOrder $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->order = $this->build(ShopOrder::class)
            ->asPending()
            ->has($this->build(ShopPayment::class), 'payment')
            ->create();
    }

    #[Test]
    public function itThrowsAnExceptionIfTheOrderIsntPending(): void
    {
        $this->order->update(['state_id' => OrderState::PAID]);

        $this->expectException(RuntimeException::class);

        $this->callAction(MarkOrderAsPaidAction::class, $this->order, $this->createCharge());
    }

    #[Test]
    public function itUpdatesTheOrderAsPaid(): void
    {
        $this->assertEquals(OrderState::PENDING, $this->order->state_id);

        $this->callAction(MarkOrderAsPaidAction::class, $this->order, $this->createCharge());

        $this->assertEquals(OrderState::PAID, $this->order->refresh()->state_id);
    }

    #[Test]
    public function itStoresThePaymentTypeAndFee(): void
    {
        $this->assertNull($this->order->payment->payment_type_id);
        $this->assertNull($this->order->payment->fee);

        $this->callAction(MarkOrderAsPaidAction::class, $this->order, $this->createCharge(fee: 50));

        $this->assertEquals('Card', $this->order->payment->payment_type_id);
        $this->assertEquals(50, $this->order->payment->fee);
    }

    #[Test]
    public function itStoresThePaymentResponse(): void
    {
        $this->callAction(MarkOrderAsPaidAction::class, $this->order, $this->createCharge());

        $this->assertNotNull($this->order->payment->payment_type_id);
    }
}
