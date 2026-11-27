<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryRecommendations\Actions;

use App\Filament\Resources\EatingOut\EateryRecommendations\Pages\ListEateryRecommendations;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryRecommendation;
use App\Models\EatingOut\NationwideBranch;
use App\Models\User;
use App\Notifications\EatingOut\EateryRecommendationAlreadyExistsNotification;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IgnoreAndSendAlreadyExistsActionTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->eatery = $this->create(Eatery::class);
    }

    #[Test]
    public function itIsVisibleForAPendingRecommendation(): void
    {
        $recommendation = $this->create(EateryRecommendation::class);

        Livewire::test(ListEateryRecommendations::class)->assertActionVisible(TestAction::make('ignoreAndSendAlreadyExists')->table($recommendation));
    }

    #[Test]
    public function itIsVisibleForAnIgnoredRecommendation(): void
    {
        $recommendation = $this->create(EateryRecommendation::class, ['ignored' => true]);

        Livewire::test(ListEateryRecommendations::class)->assertActionVisible(TestAction::make('ignoreAndSendAlreadyExists')->table($recommendation));
    }

    #[Test]
    public function itIsHiddenForACompletedRecommendation(): void
    {
        $recommendation = $this->create(EateryRecommendation::class, ['completed' => true]);

        Livewire::test(ListEateryRecommendations::class)->assertActionHidden(TestAction::make('ignoreAndSendAlreadyExists')->table($recommendation));
    }

    #[Test]
    public function itRequiresAnEateryToBeSelected(): void
    {
        $recommendation = $this->create(EateryRecommendation::class);

        Livewire::test(ListEateryRecommendations::class)
            ->callAction(TestAction::make('ignoreAndSendAlreadyExists')->table($recommendation), ['eatery' => null])
            ->assertHasFormErrors(['eatery' => 'required']);

        Notification::assertNothingSent();

        $this->assertFalse($recommendation->refresh()->ignored);
    }

    #[Test]
    public function itSendsTheEmailForAnEateryAndIgnoresTheRecommendation(): void
    {
        $recommendation = $this->create(EateryRecommendation::class);

        Livewire::test(ListEateryRecommendations::class)
            ->callAction(TestAction::make('ignoreAndSendAlreadyExists')->table($recommendation), ['eatery' => "{$this->eatery->id}:"])
            ->assertNotified();

        Notification::assertSentTo(new AnonymousNotifiable(), EateryRecommendationAlreadyExistsNotification::class);

        $this->assertTrue($recommendation->refresh()->ignored);
    }

    #[Test]
    public function itSendsTheEmailForABranchAndIgnoresTheRecommendation(): void
    {
        $branch = $this->build(NationwideBranch::class)->forEatery($this->eatery)->create();

        $recommendation = $this->create(EateryRecommendation::class);

        Livewire::test(ListEateryRecommendations::class)
            ->callAction(TestAction::make('ignoreAndSendAlreadyExists')->table($recommendation), ['eatery' => "{$this->eatery->id}:{$branch->id}"])
            ->assertNotified();

        Notification::assertSentTo(new AnonymousNotifiable(), EateryRecommendationAlreadyExistsNotification::class);

        $this->assertTrue($recommendation->refresh()->ignored);
    }

    #[Test]
    public function itDoesNotSendTheEmailForAnEateryThatIsNotLive(): void
    {
        $hidden = $this->create(Eatery::class, ['live' => false]);

        $recommendation = $this->create(EateryRecommendation::class);

        Livewire::test(ListEateryRecommendations::class)
            ->callAction(TestAction::make('ignoreAndSendAlreadyExists')->table($recommendation), ['eatery' => "{$hidden->id}:"]);

        Notification::assertNothingSent();

        $this->assertFalse($recommendation->refresh()->ignored);
    }
}
