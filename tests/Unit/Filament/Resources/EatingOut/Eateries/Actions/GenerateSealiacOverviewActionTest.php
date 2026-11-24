<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\Eateries\Actions;

use App\Actions\EatingOut\GetSealiacEateryOverviewAction;
use App\Filament\Resources\EatingOut\Eateries\Pages\EditEatery;
use App\Filament\Resources\EatingOut\Eateries\Pages\ListEateries;
use App\Filament\Resources\EatingOut\Eateries\RelationManagers\BranchesRelationManager;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\NationwideBranch;
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

    #[Test]
    public function itIsVisibleForALiveReviewedBranch(): void
    {
        $branch = $this->reviewedBranch();

        $this->listBranches($branch)->assertActionVisible($this->action($branch));
    }

    #[Test]
    public function itIsHiddenForABranchWithNoReviews(): void
    {
        $branch = $this->create(NationwideBranch::class, ['wheretoeat_id' => $this->create(Eatery::class)->id]);

        $this->listBranches($branch)->assertActionHidden($this->action($branch));
    }

    #[Test]
    public function itIsHiddenForABranchThatIsntLive(): void
    {
        $branch = $this->reviewedBranch();
        $branch->updateQuietly(['live' => false]);

        $this->listBranches($branch)->assertActionHidden($this->action($branch));
    }

    #[Test]
    public function itInvalidatesTheChainAndBranchOverviewsAndQueuesTheNewOne(): void
    {
        $branch = $this->reviewedBranch();

        $chainOverview = $this->build(SealiacOverview::class)->forEatery($branch->eatery)->create(['invalidated' => false]);
        $branchOverview = $this->build(SealiacOverview::class)->forNationwideBranch($branch)->create(['invalidated' => false]);

        $this->listBranches($branch)
            ->callAction($this->action($branch))
            ->assertNotified();

        $this->assertTrue($chainOverview->refresh()->invalidated);
        $this->assertTrue($branchOverview->refresh()->invalidated);

        $this->expectAction(GetSealiacEateryOverviewAction::class, [
            fn (Eatery $eatery, NationwideBranch $argument): bool => $eatery->is($branch->eatery) && $argument->is($branch),
        ], return: $this->build(SealiacOverview::class)->forNationwideBranch($branch)->create());

        $job = Bus::dispatched(CallQueuedClosure::class)->last();

        app()->call($job->handle(...));
    }

    protected function reviewedBranch(): NationwideBranch
    {
        $branch = $this->create(NationwideBranch::class, ['wheretoeat_id' => $this->create(Eatery::class)->id]);

        $this->create(EateryReview::class, ['wheretoeat_id' => $branch->wheretoeat_id, 'nationwide_branch_id' => $branch->id]);

        return $branch;
    }

    protected function listBranches(NationwideBranch $branch): Testable
    {
        return Livewire::test(BranchesRelationManager::class, [
            'ownerRecord' => $branch->eatery,
            'pageClass' => EditEatery::class,
        ]);
    }

    protected function reviewedEatery(): Eatery
    {
        $eatery = $this->create(Eatery::class);

        $this->create(EateryReview::class, ['wheretoeat_id' => $eatery->id]);

        return $eatery;
    }

    protected function action(Eatery|NationwideBranch $record): TestAction
    {
        return TestAction::make('generateSealiacOverview')->table($record);
    }

    protected function listEateries(): Testable
    {
        return Livewire::test(ListEateries::class);
    }
}
