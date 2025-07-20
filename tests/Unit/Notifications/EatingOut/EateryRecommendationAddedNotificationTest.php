<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications\EatingOut;

use App\Infrastructure\MjmlMessage;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryRecommendation;
use App\Notifications\EatingOut\EateryRecommendationAddedNotification;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\TestTime\TestTime;
use Tests\TestCase;

class EateryRecommendationAddedNotificationTest extends TestCase
{
    protected Eatery $eatery;

    protected EateryRecommendation $recommendation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->eatery = $this->create(Eatery::class);
        $this->recommendation = $this->create(EateryRecommendation::class);

        Notification::fake();
        TestTime::freeze();
    }

    #[Test]
    #[DataProvider('mailDataProvider')]
    public function itHasTheEmailData(callable $closure): void
    {
        (new AnonymousNotifiable())
            ->route('mail', $this->recommendation->email)
            ->notify(new EateryRecommendationAddedNotification($this->recommendation, $this->eatery));

        Notification::assertSentTo(
            new AnonymousNotifiable(),
            EateryRecommendationAddedNotification::class,
            function (EateryRecommendationAddedNotification $notification) use ($closure): bool {
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
            'has the user name' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString($test->recommendation->name, $emailContent);
            }],
            'has the eatery name' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString($test->eatery->name, $emailContent);
            }],
            'has the eatery link' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString($test->eatery->absoluteLink(), $emailContent);
            }],
        ];
    }
}
