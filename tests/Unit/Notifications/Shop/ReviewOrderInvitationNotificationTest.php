<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications\Shop;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use App\Infrastructure\MjmlMessage;
use App\Models\Shop\ShopOrder;
use App\Notifications\Shop\ReviewOrderInvitationNotification;
use Illuminate\Support\Facades\Notification;
use Spatie\TestTime\TestTime;
use Tests\TestCase;

class ReviewOrderInvitationNotificationTest extends TestCase
{
    protected ShopOrder $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->order = $this->build(ShopOrder::class)
            ->asPaid()
            ->create();

        $this->order->reviewInvitation()->create();

        Notification::fake();
        TestTime::freeze();
    }

    #[Test]
    #[DataProvider('mailDataProvider')]
    public function itHasTheOrderDate(callable $closure): void
    {
        $this->order->customer->notify(new ReviewOrderInvitationNotification($this->order, 'foobar'));

        Notification::assertSentTo(
            $this->order->customer,
            ReviewOrderInvitationNotification::class,
            function (ReviewOrderInvitationNotification $notification) use ($closure): bool {
                $mail = $notification->toMail($this->order->customer);
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
            'has the customer name' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString($test->order->address->name, $emailContent);
            }],
            'has the delay text in the constructor' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString('foobar', $emailContent);
            }],
            'has the review link' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString(route('shop.review-order', ['invitation' => $test->order->reviewInvitation]), $emailContent);
            }],
        ];
    }
}
