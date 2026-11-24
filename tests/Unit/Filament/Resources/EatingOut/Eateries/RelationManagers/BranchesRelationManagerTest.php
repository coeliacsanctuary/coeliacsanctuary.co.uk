<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\Eateries\RelationManagers;

use App\Filament\Resources\EatingOut\Eateries\Pages\EditEatery;
use App\Filament\Resources\EatingOut\Eateries\Pages\ViewEatery;
use App\Filament\Resources\EatingOut\Eateries\RelationManagers\BranchesRelationManager;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\NationwideBranch;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BranchesRelationManagerTest extends TestCase
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
    public function itIsAvailableForAChain(): void
    {
        $this->assertTrue(BranchesRelationManager::canViewForRecord($this->eatery, EditEatery::class));
    }

    #[Test]
    public function itIsNotAvailableForAnEateryThatIsntAChain(): void
    {
        $county = $this->create(EateryCounty::class, ['county' => 'Cornwall', 'country_id' => 1]);

        $this->eatery->updateQuietly(['county_id' => $county->id]);

        $this->assertFalse(BranchesRelationManager::canViewForRecord($this->eatery, EditEatery::class));
    }

    #[Test]
    #[DataProvider('pages')]
    public function itCanAddAndEditBranchesFromThePage(string $page): void
    {
        $branch = $this->create(NationwideBranch::class, ['wheretoeat_id' => $this->eatery->id]);

        $this->branchesPanel($page)
            ->assertActionVisible(TestAction::make('create')->table())
            ->assertActionVisible(TestAction::make('edit')->table($branch))
            ->assertActionVisible(TestAction::make('view')->table($branch))
            ->assertActionDoesNotExist(TestAction::make('delete')->table($branch));
    }

    public static function pages(): array
    {
        return [
            'view' => [ViewEatery::class],
            'edit' => [EditEatery::class],
        ];
    }

    #[Test]
    public function itAddsTheBranchToTheChainAndTouchesIt(): void
    {
        $this->eatery->timestamps = false;
        $this->eatery->forceFill(['updated_at' => '2020-01-01 00:00:00'])->saveQuietly();
        $this->eatery->timestamps = true;

        $this->branchesPanel()
            ->callAction(TestAction::make('create')->table(), [
                'name' => 'My Branch',
                'live' => true,
                'country_id' => 1,
                'county_id' => 1,
                'town_id' => 1,
                'address' => '1 High Street',
                'lat' => 1,
                'lng' => 2,
            ])
            ->assertHasNoFormErrors();

        $branch = NationwideBranch::query()->sole();

        $this->assertSame($this->eatery->id, $branch->wheretoeat_id);
        $this->assertSame('My Branch', $branch->name);
        $this->assertTrue($this->eatery->refresh()->updated_at->isToday());
    }

    #[Test]
    public function itTouchesTheChainWhenABranchIsEdited(): void
    {
        $branch = $this->create(NationwideBranch::class, ['wheretoeat_id' => $this->eatery->id]);

        $this->eatery->timestamps = false;
        $this->eatery->forceFill(['updated_at' => '2020-01-01 00:00:00'])->saveQuietly();
        $this->eatery->timestamps = true;

        $this->branchesPanel()
            ->callAction(TestAction::make('edit')->table($branch), ['name' => 'Renamed'])
            ->assertHasNoFormErrors();

        $this->assertSame('Renamed', $branch->refresh()->name);
        $this->assertTrue($this->eatery->refresh()->updated_at->isToday());
    }

    #[Test]
    public function itShowsTheBranchesForTheEatery(): void
    {
        $branch = $this->create(NationwideBranch::class, ['wheretoeat_id' => $this->eatery->id]);

        $this->branchesPanel()->assertCanSeeTableRecords([$branch]);
    }

    #[Test]
    public function itDoesNotShowBranchesFromAnotherEatery(): void
    {
        $mine = $this->create(NationwideBranch::class, ['wheretoeat_id' => $this->eatery->id]);
        $theirs = $this->create(NationwideBranch::class, ['wheretoeat_id' => $this->create(Eatery::class)->id]);

        $this->branchesPanel()
            ->assertCanSeeTableRecords([$mine])
            ->assertCanNotSeeTableRecords([$theirs]);
    }

    protected function branchesPanel(string $page = EditEatery::class): Testable
    {
        return Livewire::test(BranchesRelationManager::class, [
            'ownerRecord' => $this->eatery,
            'pageClass' => $page,
        ]);
    }
}
