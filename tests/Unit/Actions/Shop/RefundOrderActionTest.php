<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop;

use App\Actions\Shop\CancelOrderAction;
use App\Actions\Shop\RefundOrderAction;
use App\DataObjects\Shop\RefundOrderDto;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopPaymentRefund;
use App\Notifications\Shop\OrderRefundNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Stripe\Service\RefundService;
use Tests\Concerns\MocksStripe;
use Tests\TestCase;

class RefundOrderActionTest extends TestCase
{
    use MocksStripe;

    protected ShopOrder $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->order = $this->build(ShopOrder::class)
            ->asShipped()
            ->create();

        Notification::fake();
        Mail::fake();
    }

    #[Test]
    public function itRefundsTheOrderInStripeIfTheOrderHasAStripeChargeId(): void
    {
        $dto = new RefundOrderDto(100, 'foo', false, false, null);

        $this->mockCreateRefund($this->order->payment->response->charge_id, 100);

        app(RefundOrderAction::class)->handle($this->order, $dto);
    }

    #[Test]
    public function itDoesntRefundTheOrderInStripeIfTheOrderDoesNotHaveAStripeChargeId(): void
    {
        $dto = new RefundOrderDto(100, 'foo', false, false, null);

        $this->order->payment->response->update(['charge_id' => null]);

        $refunds = $this->partialMock(RefundService::class);
        $this->getStripeClient()->refunds = $refunds;

        $refunds->shouldNotReceive('create');

        app(RefundOrderAction::class)->handle($this->order, $dto);
    }

    #[Test]
    public function itStoresTheRefundInTheShopPaymentRefundsTable(): void
    {
        $dto = new RefundOrderDto(100, 'foo', false, false, null);

        $this->mockCreateRefund($this->order->payment->response->charge_id, 100);

        $this->assertDatabaseEmpty(ShopPaymentRefund::class);

        app(RefundOrderAction::class)->handle($this->order, $dto);

        $this->assertDatabaseCount(ShopPaymentRefund::class, 1);
    }

    #[Test]
    public function itStoresTheRefundDataFromStripeIfThereIsAChargeIdOnTheOrder(): void
    {
        $dto = new RefundOrderDto(100, 'foo', false, false, null);

        $stripeRefund = $this->mockCreateRefund($this->order->payment->response->charge_id, 100);

        app(RefundOrderAction::class)->handle($this->order, $dto);

        $refundRow = ShopPaymentRefund::query()->first();

        $this->assertEquals($stripeRefund->id, $refundRow->refund_id);
        $this->assertEquals(100, $refundRow->amount);
        $this->assertEquals('foo', $refundRow->note);
        $this->assertEquals($stripeRefund->toJSON(), $refundRow->response);
    }

    #[Test]
    public function itCanHandleTheStripeRefundDataNotExistingIfThereWasNoChargeId(): void
    {
        $dto = new RefundOrderDto(100, 'foo', false, false, null);

        $this->order->payment->response->update(['charge_id' => null]);

        $refunds = $this->partialMock(RefundService::class);
        $this->getStripeClient()->refunds = $refunds;

        $refunds->shouldNotReceive('create');

        app(RefundOrderAction::class)->handle($this->order, $dto);

        $refundRow = ShopPaymentRefund::query()->first();

        $this->assertNull($refundRow->refund_id);
        $this->assertEquals(100, $refundRow->amount);
        $this->assertEquals('foo', $refundRow->note);
        $this->assertNull($refundRow->response);
    }

    #[Test]
    public function itWillCreateTheRefundRecordUsingTheGivenDateIfOneIsSpecified(): void
    {
        $createdAt = Carbon::setTestNow('2025-06-01');

        $dto = new RefundOrderDto(100, 'foo', false, false, null, now());

        $this->order->payment->response->update(['charge_id' => null]);

        $refunds = $this->partialMock(RefundService::class);
        $this->getStripeClient()->refunds = $refunds;

        $refunds->shouldNotReceive('create');

        app(RefundOrderAction::class)->handle($this->order, $dto);

        $refundRow = ShopPaymentRefund::query()->first();

        $this->assertNull($refundRow->refund_id);
        $this->assertEquals(100, $refundRow->amount);
        $this->assertEquals('foo', $refundRow->note);
        $this->assertNull($refundRow->response);
        $this->assertTrue($refundRow->created_at->isSameDay('2025-06-01'));
    }

    #[Test]
    public function itAssociatesTheRefundToTheShopPaymentAndOrder(): void
    {
        $dto = new RefundOrderDto(100, 'foo', false, false, null);

        $this->mockCreateRefund($this->order->payment->response->charge_id, 100);

        $this->assertEmpty($this->order->payment->refunds);
        $this->assertEmpty($this->order->refunds);

        app(RefundOrderAction::class)->handle($this->order, $dto);

        $this->assertCount(1, $this->order->payment->refresh()->refunds);
        $this->assertCount(1, $this->order->refresh()->refunds);
    }

    #[Test]
    public function itCallsTheCancelOrderActionIfCancelIsTrueInTheDto(): void
    {
        $dto = new RefundOrderDto(100, 'foo', true, false, null);

        $this->mockCreateRefund($this->order->payment->response->charge_id, 100);

        $this->expectAction(CancelOrderAction::class, [function ($argOrder) {
            $this->assertTrue($this->order->is($argOrder));

            return true;
        }]);

        app(RefundOrderAction::class)->handle($this->order, $dto);
    }

    #[Test]
    public function itDoesntCallTheCancelOrderActionIfCancelIsNotTrueInTheDto(): void
    {
        $dto = new RefundOrderDto(100, 'foo', false, false, null);

        $this->mockCreateRefund($this->order->payment->response->charge_id, 100);

        $this->dontExpectAction(CancelOrderAction::class);

        app(RefundOrderAction::class)->handle($this->order, $dto);
    }

    #[Test]
    public function itNotifiesTheCustomerIfNotifyIsTrueInTheDto(): void
    {
        $dto = new RefundOrderDto(100, 'foo', false, true, 'Notification Reason');

        $this->mockCreateRefund($this->order->payment->response->charge_id, 100);

        app(RefundOrderAction::class)->handle($this->order, $dto);

        Notification::assertSentTo($this->order->customer, OrderRefundNotification::class);
    }

    #[Test]
    public function itDoesntNotifyTheCustomerIfNotifyIsFalseInTheDto(): void
    {
        $dto = new RefundOrderDto(100, 'foo', false, false, null);

        $this->mockCreateRefund($this->order->payment->response->charge_id, 100);

        app(RefundOrderAction::class)->handle($this->order, $dto);

        Notification::assertNothingSent();
    }
}
