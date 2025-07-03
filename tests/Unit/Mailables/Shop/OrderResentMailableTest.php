<?php

declare(strict_types=1);

namespace Tests\Unit\Mailables\Shop;

use App\Infrastructure\MjmlMessage;
use App\Mailables\Shop\OrderResentMailable;
use App\Models\Shop\ShopOrder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OrderResentMailableTest extends TestCase
{
    #[Test]
    public function itReturnsAnMjmlMessageInstance(): void
    {
        $this->assertInstanceOf(
            MjmlMessage::class,
            OrderResentMailable::make(new ShopOrder(), collect(), 'foo'),
        );
    }

    #[Test]
    public function itHasTheSubjectSet(): void
    {
        $mailable = OrderResentMailable::make(new ShopOrder(), collect(), 'foo');

        $this->assertEquals('Your Coeliac Sanctuary order has been resent!', $mailable->subject);
    }

    #[Test]
    public function itHasTheCorrectView(): void
    {
        $mailable = OrderResentMailable::make(new ShopOrder(), collect(), 'foo');

        $this->assertEquals('mailables.mjml.shop.order-resent', $mailable->mjml);
    }

    #[Test]
    public function itHasTheCorrectData(): void
    {
        $order = $this->build(ShopOrder::class)->asPaid()->create();

        $data = [
            'order' => fn ($assertionOrder) => $this->assertTrue($order->is($assertionOrder)),
            'notifiable' => fn ($customer) => $this->assertTrue($order->customer->is($customer)),
            'reason' => fn ($reason) => $this->assertEquals('to let you know your Coeliac Sanctuary order has been resent!', $reason),
        ];

        $mailable = OrderResentMailable::make($order, collect(), 'foo');
        $emailData = $mailable->data();

        foreach ($data as $key => $closure) {
            $this->assertArrayHasKey($key, $emailData);
            $closure($emailData[$key]);
        }
    }
}
