<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\Eateries\Schemas;

use App\Filament\Resources\EatingOut\Eateries\Pages\EditEatery;
use App\Filament\Resources\EatingOut\Eateries\RelationManagers\BranchesRelationManager;
use App\Filament\Resources\EatingOut\Eateries\RelationManagers\ReportsRelationManager;
use App\Filament\Resources\EatingOut\Eateries\RelationManagers\ReviewsRelationManager;
use App\Filament\Resources\EatingOut\Eateries\RelationManagers\SealiacOverviewsRelationManager;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryReport;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\NationwideBranch;
use App\Models\SealiacOverview;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NationwideBranchInfolistTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->eatery = $this->create(Eatery::class);
    }

    #[Test]
    public function itShowsTheBranchAndItsPanels(): void
    {
        $branch = $this->branch(['name' => 'My Branch', 'address' => '1 High Street']);

        $this->branchesPanel()
            ->mountAction(TestAction::make('view')->table($branch))
            ->assertMountedActionModalSee(['My Branch', '1 High Street', 'Crewe, Cheshire, England'])
            ->assertMountedActionModalSee([
                $this->componentName(SealiacOverviewsRelationManager::class),
                $this->componentName(ReviewsRelationManager::class),
                $this->componentName(ReportsRelationManager::class),
            ]);
    }

    #[Test]
    public function itOnlyShowsTheOverviewsForTheBranch(): void
    {
        $branch = $this->branch();

        $mine = $this->build(SealiacOverview::class)->forNationwideBranch($branch)->create();
        $theirs = $this->build(SealiacOverview::class)->forNationwideBranch($this->branch())->create();
        $chain = $this->build(SealiacOverview::class)->forEatery($this->eatery)->create();

        $this->panel(SealiacOverviewsRelationManager::class, $branch)
            ->assertCanSeeTableRecords([$mine])
            ->assertCanNotSeeTableRecords([$theirs, $chain]);
    }

    #[Test]
    public function itOnlyShowsTheReviewsForTheBranchIncludingPendingOnes(): void
    {
        $branch = $this->branch();

        $mine = $this->create(EateryReview::class, ['wheretoeat_id' => $this->eatery->id, 'nationwide_branch_id' => $branch->id]);
        $pending = $this->create(EateryReview::class, ['wheretoeat_id' => $this->eatery->id, 'nationwide_branch_id' => $branch->id, 'approved' => false, 'review' => 'Pending']);
        $theirs = $this->create(EateryReview::class, ['wheretoeat_id' => $this->eatery->id, 'nationwide_branch_id' => $this->branch()->id]);

        $this->panel(ReviewsRelationManager::class, $branch)
            ->assertCanSeeTableRecords([$mine, $pending])
            ->assertCanNotSeeTableRecords([$theirs]);
    }

    #[Test]
    public function itOnlyShowsTheReportsForTheBranch(): void
    {
        $branch = $this->branch();

        $mine = $this->create(EateryReport::class, ['wheretoeat_id' => $this->eatery->id, 'branch_id' => $branch->id]);
        $theirs = $this->create(EateryReport::class, ['wheretoeat_id' => $this->eatery->id, 'branch_id' => $this->branch()->id]);

        $this->panel(ReportsRelationManager::class, $branch)
            ->assertCanSeeTableRecords([$mine])
            ->assertCanNotSeeTableRecords([$theirs]);
    }

    protected function componentName(string $component): string
    {
        return mb_trim((string) json_encode(['name' => app('livewire.factory')->resolveComponentName($component)]), '{}');
    }

    protected function panel(string $relationManager, NationwideBranch $branch): Testable
    {
        return Livewire::test($relationManager, ['ownerRecord' => $branch, 'pageClass' => EditEatery::class]);
    }

    protected function branch(array $attributes = []): NationwideBranch
    {
        return $this->create(NationwideBranch::class, ['wheretoeat_id' => $this->eatery->id, ...$attributes]);
    }

    protected function branchesPanel(): Testable
    {
        return Livewire::test(BranchesRelationManager::class, [
            'ownerRecord' => $this->eatery,
            'pageClass' => EditEatery::class,
        ]);
    }
}
