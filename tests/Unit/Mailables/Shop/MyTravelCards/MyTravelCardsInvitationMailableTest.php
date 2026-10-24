<?php

declare(strict_types=1);

namespace Tests\Unit\Mailables\Shop\MyTravelCards;

use App\Infrastructure\MjmlMessage;
use App\Mailables\Shop\MyTravelCards\MyTravelCardsInvitationMailable;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use PHPUnit\Framework\Attributes\Test;
use Spatie\TestTime\TestTime;
use Tests\TestCase;

class MyTravelCardsInvitationMailableTest extends TestCase
{
    protected ShopCustomer $customer;

    protected ShopOrder $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = $this->create(ShopCustomer::class);

        $this->order = $this->build(ShopOrder::class)
            ->asPaid($this->customer)
            ->create();

        TestTime::freeze();
    }

    #[Test]
    public function itReturnsAnMjmlMessageInstance(): void
    {
        $this->assertInstanceOf(
            MjmlMessage::class,
            MyTravelCardsInvitationMailable::make($this->order, 'foo'),
        );
    }

    #[Test]
    public function itHasTheSubjectSet(): void
    {
        $mailable = MyTravelCardsInvitationMailable::make($this->order, 'foo');

        $this->assertEquals('Your Coeliac Sanctuary digital cards are ready!', $mailable->subject);
    }

    #[Test]
    public function itHasTheCorrectView(): void
    {
        $mailable = MyTravelCardsInvitationMailable::make($this->order, 'foo');

        $this->assertEquals('mailables.mjml.shop.my-travel-cards.invitation', $mailable->mjml);
    }

    #[Test]
    public function itHasTheCorrectData(): void
    {
        $data = [
            'key' => fn ($key) => $this->assertEquals('foo', $key),
            'order' => fn ($assertionOrder) => $this->assertTrue($this->order->is($assertionOrder)),
            'notifiable' => fn ($customer) => $this->assertTrue($this->customer->is($customer)),
            'reason' => fn ($reason) => $this->assertEquals('so you can access your digital cards purchased through Coeliac Sanctuary Shop.', $reason),
            'downloadLink' => fn ($downloadLink) => $this->assertStringStartsWith(
                route('my-travel-cards.login.order.get', ['order' => $this->order]),
                $downloadLink,
            ),
        ];

        $mailable = MyTravelCardsInvitationMailable::make($this->order, 'foo');
        $emailData = $mailable->data();

        foreach ($data as $key => $closure) {
            $this->assertArrayHasKey($key, $emailData);
            $closure($emailData[$key]);
        }
    }

    #[Test]
    public function itHasAValidlySignedDownloadLink(): void
    {
        $mailable = MyTravelCardsInvitationMailable::make($this->order, 'foo');

        $this->assertTrue(URL::hasValidSignature(Request::create($mailable->data()['downloadLink'])));
    }

    #[Test]
    public function theDownloadLinkExpiresInAWeek(): void
    {
        $mailable = MyTravelCardsInvitationMailable::make($this->order, 'foo');

        parse_str((string) parse_url($mailable->data()['downloadLink'], PHP_URL_QUERY), $query);

        $this->assertSame(now()->addWeek()->timestamp, (int) $query['expires']);
    }
}
