<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications\Shop\MyTravelCards;

use App\Infrastructure\MjmlMessage;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrder;
use App\Notifications\Shop\MyTravelCards\MyTravelCardsInvitationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\TestTime\TestTime;
use Tests\TestCase;

class MyTravelCardsInvitationNotificationTest extends TestCase
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

        Notification::fake();
        TestTime::freeze();
    }

    #[Test]
    #[DataProvider('mailDataProvider')]
    public function itHasTheEmailData(callable $closure): void
    {
        $this->customer->notify(new MyTravelCardsInvitationNotification($this->order));

        Notification::assertSentTo(
            $this->customer,
            MyTravelCardsInvitationNotification::class,
            function (MyTravelCardsInvitationNotification $notification) use ($closure): bool {
                $mail = $notification->toMail($this->customer);
                $content = $mail->render();

                $closure($this, $mail, $content);

                return true;
            }
        );
    }

    public static function mailDataProvider(): array
    {
        return [
            'has the email key' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString($message->data()['key'], $emailContent);
            }],
            'has the subject' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertSame('Your Coeliac Sanctuary digital cards are ready!', $message->subject);
            }],
            'has the header' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString('Access your Digital Travel Cards!', $emailContent);
            }],
            'has the customer name' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString($test->order->customer->name, $emailContent);
            }],
            'has the order number' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString((string) $test->order->order_key, $emailContent);
            }],
            'has the reason for the email' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString('so you can access your digital cards purchased through Coeliac Sanctuary Shop.', $emailContent);
            }],
            'has the download link' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString(
                    route('my-travel-cards.login.order.get', ['order' => $test->order]),
                    $emailContent
                );
            }],
            'has a validly signed download link' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertTrue(URL::hasValidSignature(Request::create($message->data()['downloadLink'])));
            }],
            'has a download link that expires in a week' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                parse_str((string) parse_url($message->data()['downloadLink'], PHP_URL_QUERY), $query);

                $test->assertSame(now()->addWeek()->timestamp, (int) $query['expires']);
            }],
            'has a link to the my travel cards page' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString(route('my-travel-cards.index'), $emailContent);
            }],
        ];
    }
}
