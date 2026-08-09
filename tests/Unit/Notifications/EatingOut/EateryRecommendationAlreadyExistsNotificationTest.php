<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications\EatingOut;

use App\Infrastructure\MjmlMessage;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryRecommendation;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\EateryVenueType;
use App\Models\EatingOut\NationwideBranch;
use App\Notifications\EatingOut\EateryRecommendationAlreadyExistsNotification;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Spatie\TestTime\TestTime;
use Tests\TestCase;

class EateryRecommendationAlreadyExistsNotificationTest extends TestCase
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
            ->notify(new EateryRecommendationAlreadyExistsNotification($this->recommendation, $this->eatery));

        Notification::assertSentTo(
            new AnonymousNotifiable(),
            EateryRecommendationAlreadyExistsNotification::class,
            function (EateryRecommendationAlreadyExistsNotification $notification) use ($closure): bool {
                $mail = $notification->toMail(new AnonymousNotifiable());
                $content = $mail->render();

                $closure($this, $mail, $content);

                return true;
            }
        );
    }

    #[Test]
    public function itRendersACardForEachNearbyEateryInTheBranchesTown(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);

        /** @var EateryTown $branchTown */
        $branchTown = $this->create(EateryTown::class, ['town' => 'Nantwich']);

        /** @var EateryVenueType $venueType */
        $venueType = EateryVenueType::query()->findOrFail(2);

        /** @var Eatery $nearby */
        $nearby = $this->create(Eatery::class, [
            'town_id' => $branchTown->id,
            'venue_type_id' => $venueType->id,
            'address' => "12 Market Street\nNantwich\nCW5 5AB",
        ]);

        /** @var Eatery $eatery */
        $eatery = $this->eatery->fresh();

        /** @var NationwideBranch $branch */
        $branch = $this->build(NationwideBranch::class)->forEatery($eatery)->create(['town_id' => $branchTown->id]);

        $content = (new EateryRecommendationAlreadyExistsNotification($this->recommendation, $eatery, $branch))
            ->toMail(new AnonymousNotifiable())
            ->render();

        $this->assertStringContainsString('More gluten free places in Nantwich', $content);
        $this->assertStringContainsString($nearby->name, $content);
        $this->assertStringContainsString($venueType->venue_type, $content);
        $this->assertStringContainsString('12 Market Street', $content);
        $this->assertStringContainsString($nearby->absoluteLink(), $content);
        $this->assertStringContainsString($branchTown->absoluteLink(), $content);
    }

    #[Test]
    public function itNamesTheBranchesTownInTheCopyWhenTheBranchHasNoNameOfItsOwn(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);

        /** @var EateryTown $branchTown */
        $branchTown = $this->create(EateryTown::class, ['town' => 'Nantwich']);

        $this->create(Eatery::class, ['town_id' => $branchTown->id]);

        /** @var Eatery $eatery */
        $eatery = $this->eatery->fresh();

        /** @var NationwideBranch $branch */
        $branch = $this->build(NationwideBranch::class)
            ->forEatery($eatery)
            ->create(['town_id' => $branchTown->id, 'name' => null]);

        $content = (new EateryRecommendationAlreadyExistsNotification($this->recommendation, $eatery, $branch))
            ->toMail(new AnonymousNotifiable())
            ->render();

        $this->assertStringContainsString(
            "Why don't you checkout the <strong>Nantwich</strong> branch of <strong>{$eatery->name}</strong>",
            $content,
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
