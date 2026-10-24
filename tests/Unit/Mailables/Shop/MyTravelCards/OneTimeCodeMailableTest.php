<?php

declare(strict_types=1);

namespace Tests\Unit\Mailables\Shop\MyTravelCards;

use App\Infrastructure\MjmlMessage;
use App\Mailables\Shop\MyTravelCards\OneTimeCodeMailable;
use App\Models\Shop\ShopCustomer;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class OneTimeCodeMailableTest extends TestCase
{
    protected ShopCustomer $customer;

    public function setUp(): void
    {
        parent::setUp();

        $this->customer = $this->create(ShopCustomer::class);
    }

    #[Test]
    public function itReturnsAnMjmlMessageInstance(): void
    {
        $this->assertInstanceOf(
            MjmlMessage::class,
            OneTimeCodeMailable::make('123456', $this->customer, 'foo'),
        );
    }

    #[Test]
    public function itHasTheSubjectSet(): void
    {
        $mailable = OneTimeCodeMailable::make('123456', $this->customer, 'foo');

        $this->assertEquals('Your one time code to access your Travel Cards', $mailable->subject);
    }

    #[Test]
    public function itHasTheCorrectView(): void
    {
        $mailable = OneTimeCodeMailable::make('123456', $this->customer, 'foo');

        $this->assertEquals('mailables.mjml.shop.my-travel-cards.confirm', $mailable->mjml);
    }

    #[Test]
    public function itHasTheCorrectData(): void
    {
        $data = [
            'customer' => fn ($assertionCustomer) => $this->assertTrue($this->customer->is($assertionCustomer)),
            'code' => fn ($code) => $this->assertEquals('123456', $code),
            'email' => fn ($email) => $this->assertEquals($this->customer->email, $email),
            'reason' => fn ($reason) => $this->assertEquals('to provide you with a one time access code to access your Digital Travel Cards.', $reason),
            'relatedItems' => fn ($relatedItems) => $this->assertNull($relatedItems),
            'key' => fn ($key) => $this->assertEquals('foo', $key),
        ];

        $mailable = OneTimeCodeMailable::make('123456', $this->customer, 'foo');
        $emailData = $mailable->data();

        foreach ($data as $key => $closure) {
            $this->assertArrayHasKey($key, $emailData);
            $closure($emailData[$key]);
        }
    }
}
