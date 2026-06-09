<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications\EatingOut;

use App\Infrastructure\MjmlMessage;
use App\Models\EatingOut\EateryRecommendation;
use App\Notifications\EatingOut\EateryRecommendationAddedSmallBusinessNotification;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\TestTime\TestTime;
use Tests\TestCase;

class EateryRecommendationAddedSmallBusinessNotificationTest extends TestCase
{
    protected EateryRecommendation $recommendation;

    protected function setUp(): void
    {
        parent::setUp();

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
            ->notify(new EateryRecommendationAddedSmallBusinessNotification($this->recommendation));

        Notification::assertSentTo(
            new AnonymousNotifiable(),
            EateryRecommendationAddedSmallBusinessNotification::class,
            function (EateryRecommendationAddedSmallBusinessNotification $notification) use ($closure): bool {
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
            'has the place name' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString($test->recommendation->place_name, $emailContent);
            }],
            'has the blog link' => [function (self $test, MjmlMessage $message, string $emailContent): void {
                $test->assertStringContainsString('/blog/gluten-free-small-business-online-independent-retailers-market-stalls-home-bakers', $emailContent);
            }],
        ];
    }
}
