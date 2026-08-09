<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications\EatingOut;

use App\Infrastructure\MjmlMessage;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryRecommendation;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\EateryVenueType;
use App\Notifications\EatingOut\EateryRecommendationAddedNotification;
use Database\Seeders\EateryScaffoldingSeeder;
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

    #[Test]
    public function itRendersACardForEachNearbyEateryWithALinkToTheTown(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);

        /** @var EateryTown $town */
        $town = EateryTown::query()->findOrFail(1);

        /** @var EateryVenueType $venueType */
        $venueType = EateryVenueType::query()->findOrFail(2);

        /** @var Eatery $nearby */
        $nearby = $this->create(Eatery::class, [
            'venue_type_id' => $venueType->id,
            'address' => "12 Market Street\nCrewe\nCW1 2AB",
        ]);

        $this->build(EateryReview::class)->count(2)->approved()->on($nearby)->create(['rating' => 4]);

        /** @var Eatery $eatery */
        $eatery = $this->eatery->fresh();

        $content = (new EateryRecommendationAddedNotification($this->recommendation, $eatery))
            ->toMail(new AnonymousNotifiable())
            ->render();

        $this->assertStringContainsString("More gluten free places in {$town->town}", $content);
        $this->assertStringContainsString($nearby->name, $content);
        $this->assertStringContainsString($venueType->venue_type, $content);
        $this->assertStringContainsString('★ 4.0 (2 reviews)', $content);
        $this->assertStringContainsString('12 Market Street', $content);
        $this->assertStringContainsString($nearby->absoluteLink(), $content);
        $this->assertStringContainsString($town->absoluteLink(), $content);
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
