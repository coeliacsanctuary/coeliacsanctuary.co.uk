<?php

declare(strict_types=1);

namespace Tests\Unit\Mailables\Shop;

use App\Mailables\Shop\OrderRefundMailable;
use App\Models\Shop\ShopPaymentRefund;
use PHPUnit\Framework\Attributes\Test;
use App\Infrastructure\MjmlMessage;
use App\Models\Shop\ShopOrder;
use Tests\TestCase;

class OrderRefundMailableTest extends TestCase
{
    protected ShopOrder $order;

    protected ShopPaymentRefund $refund;

    protected function setUp(): void
    {
        parent::setUp();

        $this->order = $this->build(ShopOrder::class)->asShipped()->create();
        $this->refund = $this->create(ShopPaymentRefund::class, [
            'payment_id' => $this->order->payment->id,
        ]);
    }

    #[Test]
    public function itReturnsAnMjmlMessageInstance(): void
    {
        $this->assertInstanceOf(
            MjmlMessage::class,
            OrderRefundMailable::make($this->refund, 'foo', 'bar'),
        );
    }

    #[Test]
    public function itHasTheSubjectSet(): void
    {
        $mailable = OrderRefundMailable::make($this->refund, 'foo', 'bar');

        $this->assertEquals('Your Coeliac Sanctuary order has received a refund', $mailable->subject);
    }

    #[Test]
    public function itHasTheCorrectView(): void
    {
        $mailable = OrderRefundMailable::make($this->refund, 'foo', 'bar');

        $this->assertEquals('mailables.mjml.shop.order-refund', $mailable->mjml);
    }

    #[Test]
    public function itHasTheCorrectData(): void
    {
        $data = [
            'order' => fn ($assertionOrder) => $this->assertTrue($this->order->is($assertionOrder)),
            'refund' => fn($assertionRefund) => $this->assertTrue($this->refund->is($assertionRefund)),
            'refundReason' => fn($refundReason) => $this->assertEquals('this is the reason', $refundReason),
            'notifiable' => fn ($customer) => $this->assertTrue($this->order->customer->is($customer)),
            'reason' => fn ($reason) => $this->assertEquals('to let you know your Coeliac Sanctuary order has been cancelled.', $reason),
        ];

        $mailable = OrderRefundMailable::make($this->refund, 'this is the reason', 'bar');
        $emailData = $mailable->data();

        foreach ($data as $key => $closure) {
            $this->assertArrayHasKey($key, $emailData);
            $closure($emailData[$key]);
        }
    }
}
