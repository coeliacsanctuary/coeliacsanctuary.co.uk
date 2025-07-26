<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications\EatingOut;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use App\Infrastructure\MjmlMessage;
use App\Models\EatingOut\EateryReview;
use App\Notifications\EatingOut\EateryReviewApprovedNotification;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Spatie\TestTime\TestTime;
use Tests\TestCase;

class EateryReviewApprovedNotificationTest extends TestCase
{
    protected EateryReview $eateryReview;

    public function setUp(): void
    {
        parent::setUp();

        $this->eateryReview = $this->create(EateryReview::class);

        Notification::fake();
        TestTime::freeze();
    }

    #[Test]
    #[DataProvider('mailDataProvider')]
    public function itHasTheEmailData(callable $closure): void
    {
        (new AnonymousNotifiable())
            ->route('mail', $this->eateryReview->email)
            ->notify(new EateryReviewApprovedNotification($this->eateryReview));

        Notification::assertSentTo(
            new AnonymousNotifiable(),
            EateryReviewApprovedNotification::class,
            function (EateryReviewApprovedNotification $notification) use ($closure): bool {
                $mail = $notification->toMail(new AnonymousNotifiable());
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
            'has the review body' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString($test->eateryReview->review, $emailContent);
            }],
        ];
    }
}
