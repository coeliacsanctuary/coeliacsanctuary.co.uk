<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\Eateries\Actions;

use App\Filament\Resources\EatingOut\Eateries\Pages\ListEateries;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryReview;
use App\Models\SealiacOverview;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Queue\CallQueuedClosure;
use Illuminate\Support\Facades\Bus;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GenerateSealiacOverviewActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        Bus::fake();
    }

    #[Test]
    public function itIsVisibleForALiveReviewedEatery(): void
    {
        $this->listEateries()->assertActionVisible($this->action($this->reviewedEatery()));
    }

    #[Test]
    public function itIsHiddenForAnEateryWithNoReviews(): void
    {
        $this->listEateries()->assertActionHidden($this->action($this->create(Eatery::class)));
    }

    #[Test]
    public function itIsHiddenForAnEateryThatIsntLive(): void
    {
        $eatery = $this->reviewedEatery();
        $eatery->updateQuietly(['live' => false]);

        $this->listEateries()->assertActionHidden($this->action($eatery));
    }

    #[Test]
    public function itIsHiddenForAnEateryThatHasClosedDown(): void
    {
        $eatery = $this->reviewedEatery();
        $eatery->updateQuietly(['closed_down' => true]);

        $this->listEateries()->assertActionHidden($this->action($eatery));
    }

    #[Test]
    public function itQueuesTheOverview(): void
    {
        $this->listEateries()
            ->callAction($this->action($this->reviewedEatery()))
            ->assertNotified();

        Bus::assertDispatched(CallQueuedClosure::class);
    }

    #[Test]
    public function itInvalidatesTheExistingOverview(): void
    {
        $eatery = $this->reviewedEatery();

        $overview = $this->build(SealiacOverview::class)->forEatery($eatery)->create(['invalidated' => false]);

        $this->listEateries()->callAction($this->action($eatery));

        $this->assertTrue($overview->refresh()->invalidated);
    }

    protected function reviewedEatery(): Eatery
    {
        $eatery = $this->create(Eatery::class);

        $this->create(EateryReview::class, ['wheretoeat_id' => $eatery->id]);

        return $eatery;
    }

    protected function action(Eatery $eatery): TestAction
    {
        return TestAction::make('generateSealiacOverview')->table($eatery);
    }

    protected function listEateries(): Testable
    {
        return Livewire::test(ListEateries::class);
    }
}
