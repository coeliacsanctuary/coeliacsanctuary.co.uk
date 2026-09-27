<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications\Shop\MyTravelCards;

use App\Infrastructure\MjmlMessage;
use App\Models\Shop\ShopCustomer;
use App\Notifications\Shop\MyTravelCards\OneTimeCodeNotification;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\TestTime\TestTime;
use Tests\TestCase;

class OneTimeCodeNotificationTest extends TestCase
{
    protected ShopCustomer $customer;

    public function setUp(): void
    {
        parent::setUp();

        $this->customer = $this->create(ShopCustomer::class);

        Notification::fake();
        TestTime::freeze();
    }

    #[Test]
    #[DataProvider('mailDataProvider')]
    public function itHasTheEmailData(callable $closure): void
    {
        $this->customer->notify(new OneTimeCodeNotification('123456', $this->customer));

        Notification::assertSentTo(
            $this->customer,
            OneTimeCodeNotification::class,
            function (OneTimeCodeNotification $notification) use ($closure): bool {
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
                $test->assertSame('Your one time code to access your Travel Cards', $message->subject);
            }],
            'has the one time code' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString('123456', $emailContent);
            }],
            'has the customer email' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString($test->customer->email, $emailContent);
            }],
            'has the reason for the email' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString('to provide you with a one time access code to access your Digital Travel Cards', $emailContent);
            }],
        ];
    }
}
