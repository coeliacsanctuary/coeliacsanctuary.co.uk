<?php

declare(strict_types=1);

namespace Tests\Unit\Mailables\Shop;

use PHPUnit\Framework\Attributes\Test;
use App\Infrastructure\MjmlMessage;
use App\Mailables\Shop\OrderConfirmedMailable;
use App\Models\Shop\ShopHoliday;
use App\Models\Shop\ShopOrder;
use Tests\TestCase;

class OrderConfirmedMailableTest extends TestCase
{
    #[Test]
    public function itReturnsAnMjmlMessageInstance(): void
    {
        $this->assertInstanceOf(
            MjmlMessage::class,
            OrderConfirmedMailable::make(new ShopOrder(), 'foo'),
        );
    }

    #[Test]
    public function itHasTheSubjectSet(): void
    {
        $mailable = OrderConfirmedMailable::make(new ShopOrder(), 'foo');

        $this->assertEquals('Your Coeliac Sanctuary order is confirmed!', $mailable->subject);
    }

    #[Test]
    public function itHasTheCorrectView(): void
    {
        $mailable = OrderConfirmedMailable::make(new ShopOrder(), 'foo');

        $this->assertEquals('mailables.mjml.shop.order-complete', $mailable->mjml);
    }

    #[Test]
    public function itHasTheCorrectData(): void
    {
        $order = $this->build(ShopOrder::class)->asPaid()->create();

        $data = [
            'order' => fn ($assertionOrder) => $this->assertTrue($order->is($assertionOrder)),
            'notifiable' => fn ($customer) => $this->assertTrue($order->customer->is($customer)),
            'reason' => fn ($reason) => $this->assertEquals('as confirmation to an order placed in the Coeliac Sanctuary Shop.', $reason),
        ];

        $mailable = OrderConfirmedMailable::make($order, 'foo');
        $emailData = $mailable->data();

        foreach ($data as $key => $closure) {
            $this->assertArrayHasKey($key, $emailData);
            $closure($emailData[$key]);
        }
    }

    #[Test]
    public function itIncludesTheNoticeOfAnActiveShopHoliday(): void
    {
        $holiday = $this->create(ShopHoliday::class);
        $order = $this->build(ShopOrder::class)->asPaid()->create();

        $mailable = OrderConfirmedMailable::make($order, 'foo');

        $this->assertEquals($holiday->notice, $mailable->data()['holidayNotice']);
    }

    #[Test]
    public function itHasANullHolidayNoticeWhenThereIsntAnActiveHoliday(): void
    {
        $order = $this->build(ShopOrder::class)->asPaid()->create();

        $mailable = OrderConfirmedMailable::make($order, 'foo');

        $this->assertNull($mailable->data()['holidayNotice']);
    }
}
