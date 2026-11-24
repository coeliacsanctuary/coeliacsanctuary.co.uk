<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\Eateries\Tables;

use App\Filament\Resources\EatingOut\Eateries\Pages\EditEatery;
use App\Filament\Resources\EatingOut\Eateries\RelationManagers\BranchesRelationManager;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Facades\Filament;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NationwideBranchesTableTest extends TestCase
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
    #[DataProvider('columns')]
    public function itShowsTheColumn(string $column): void
    {
        $this->branchesPanel()->assertTableColumnExists($column);
    }

    public static function columns(): array
    {
        return [
            'name' => ['name'],
            'location' => ['full_location'],
            'reviews count' => ['reviews_count'],
            'live' => ['live'],
        ];
    }

    #[Test]
    public function itListsBranchesThatArentLive(): void
    {
        $branch = $this->branch(['live' => false]);

        $this->branchesPanel()->assertCanSeeTableRecords([$branch]);
    }

    #[Test]
    public function itCountsReviewsThatArentApproved(): void
    {
        $branch = $this->branch();

        $this->review($branch, ['approved' => false, 'review' => 'Pending']);

        $this->branchesPanel()->assertTableColumnStateSet('reviews_count', 1, $branch);
    }

    #[Test]
    public function itSortsByCountyThenTownThenAreaByDefault(): void
    {
        $this->locations();

        $last = $this->branch(['county_id' => 3, 'town_id' => 3, 'area_id' => 3]);
        $third = $this->branch(['county_id' => 3, 'town_id' => 3, 'area_id' => 4]);
        $second = $this->branch(['county_id' => 3, 'town_id' => 4]);
        $first = $this->branch(['county_id' => 2, 'town_id' => 2]);

        $this->branchesPanel()->assertCanSeeTableRecords([$first, $second, $third, $last], inOrder: true);
    }

    #[Test]
    public function itCanSortByNameInsteadOfTheDefaultOrder(): void
    {
        $this->locations();

        $second = $this->branch(['name' => 'Zzz', 'county_id' => 2, 'town_id' => 2]);
        $first = $this->branch(['name' => 'Aaa', 'county_id' => 3, 'town_id' => 3]);

        $this->branchesPanel()
            ->sortTable('name')
            ->assertCanSeeTableRecords([$first, $second], inOrder: true);
    }

    #[Test]
    #[DataProvider('searches')]
    public function itSearchesBy(string $search): void
    {
        $this->locations();

        $match = $this->branch(['name' => 'Gluten Free', 'address' => '9 Findme Road', 'county_id' => 3, 'town_id' => 3, 'area_id' => 3]);
        $other = $this->branch(['name' => 'Other', 'address' => 'Nowhere', 'county_id' => 2, 'town_id' => 2]);

        $this->branchesPanel()
            ->searchTable($search)
            ->assertCanSeeTableRecords([$match])
            ->assertCanNotSeeTableRecords([$other]);
    }

    public static function searches(): array
    {
        return [
            'name' => ['Gluten'],
            'address' => ['Findme'],
            'town' => ['Hackney'],
            'county' => ['London'],
            'area' => ['Shoreditch'],
        ];
    }

    #[Test]
    public function itSearchesById(): void
    {
        $match = $this->branch(['id' => 98765, 'address' => 'Somewhere']);
        $other = $this->branch(['address' => 'Nowhere']);

        $this->branchesPanel()
            ->searchTable('98765')
            ->assertCanSeeTableRecords([$match])
            ->assertCanNotSeeTableRecords([$other]);
    }

    #[Test]
    public function itDoesNotFindBranchesFromAnotherChainWhenSearching(): void
    {
        $theirs = $this->create(NationwideBranch::class, ['wheretoeat_id' => $this->create(Eatery::class)->id, 'name' => 'Gluten Free']);

        $this->branchesPanel()
            ->searchTable('Gluten')
            ->assertCanNotSeeTableRecords([$theirs]);
    }

    #[Test]
    public function itFiltersByLive(): void
    {
        $live = $this->branch();
        $notLive = $this->branch(['live' => false]);

        $this->branchesPanel()
            ->filterTable('live', true)
            ->assertCanSeeTableRecords([$live])
            ->assertCanNotSeeTableRecords([$notLive]);
    }

    #[Test]
    public function itFiltersByReviewed(): void
    {
        $reviewed = $this->branch();
        $notReviewed = $this->branch();

        $this->review($reviewed, ['approved' => false, 'review' => 'Pending']);

        $this->branchesPanel()
            ->filterTable('reviewed', true)
            ->assertCanSeeTableRecords([$reviewed])
            ->assertCanNotSeeTableRecords([$notReviewed])
            ->filterTable('reviewed', false)
            ->assertCanSeeTableRecords([$notReviewed])
            ->assertCanNotSeeTableRecords([$reviewed]);
    }

    protected function review(NationwideBranch $branch, array $attributes = []): void
    {
        $this->create(EateryReview::class, ['wheretoeat_id' => $this->eatery->id, 'nationwide_branch_id' => $branch->id, ...$attributes]);
    }

    protected function locations(): void
    {
        $this->create(EateryCounty::class, ['id' => 2, 'county' => 'Cornwall', 'country_id' => 1]);
        $this->create(EateryTown::class, ['id' => 2, 'town' => 'Truro', 'county_id' => 2]);
        $this->create(EateryCounty::class, ['id' => 3, 'county' => 'London', 'slug' => 'london', 'country_id' => 1]);
        $this->create(EateryTown::class, ['id' => 3, 'town' => 'Hackney', 'county_id' => 3]);
        $this->create(EateryTown::class, ['id' => 4, 'town' => 'Camden', 'county_id' => 3]);
        $this->create(EateryArea::class, ['id' => 3, 'area' => 'Shoreditch', 'town_id' => 3]);
        $this->create(EateryArea::class, ['id' => 4, 'area' => 'Dalston', 'town_id' => 3]);
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
