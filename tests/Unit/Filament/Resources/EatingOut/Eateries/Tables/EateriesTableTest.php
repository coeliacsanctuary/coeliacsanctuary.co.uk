<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\Eateries\Tables;

use App\Filament\Resources\EatingOut\Eateries\Pages\ListEateries;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EateryTown;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateriesTableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    #[DataProvider('columns')]
    public function itShowsTheColumn(string $column): void
    {
        $this->create(Eatery::class);

        $this->listEateries()->assertTableColumnExists($column);
    }

    public static function columns(): array
    {
        return [
            'name' => ['name'],
            'location' => ['full_location'],
            'reviews count' => ['reviews_count'],
            'type' => ['type.name'],
            'live' => ['live'],
            'closed down' => ['closed_down'],
        ];
    }

    #[Test]
    public function itDoesNotShowAnyToggleableColumns(): void
    {
        $this->create(Eatery::class);

        $columns = $this->listEateries()->instance()->getTable()->getColumns();

        foreach ($columns as $column) {
            $this->assertFalse($column->isToggleable(), "The [{$column->getName()}] column is toggleable.");
        }
    }

    #[Test]
    public function itShowsTheReviewCount(): void
    {
        $eatery = $this->create(Eatery::class);

        $this->create(EateryReview::class, 3, ['wheretoeat_id' => $eatery->id]);

        $this->listEateries()->assertTableColumnStateSet('reviews_count', 3, $eatery);
    }

    #[Test]
    public function itSortsByCountyThenTownByDefault(): void
    {
        $this->create(EateryCounty::class, ['id' => 2, 'county' => 'Zzz County', 'country_id' => 1]);
        $this->create(EateryTown::class, ['id' => 2, 'town' => 'Zzz Town', 'county_id' => 2]);
        $this->create(EateryCounty::class, ['id' => 3, 'county' => 'Aaa County', 'country_id' => 1]);
        $this->create(EateryTown::class, ['id' => 3, 'town' => 'Aaa Town', 'county_id' => 3]);

        $last = $this->create(Eatery::class, ['county_id' => 2, 'town_id' => 2]);
        $first = $this->create(Eatery::class, ['county_id' => 3, 'town_id' => 3]);

        $this->listEateries()->assertCanSeeTableRecords([$first, $last], inOrder: true);
    }

    #[Test]
    public function itFiltersByLive(): void
    {
        $live = $this->create(Eatery::class);
        $notLive = $this->build(Eatery::class)->notLive()->create();

        $this->listEateries()
            ->filterTable('live', true)
            ->assertCanSeeTableRecords([$live])
            ->assertCanNotSeeTableRecords([$notLive]);
    }

    #[Test]
    public function itFiltersByClosedDown(): void
    {
        $open = $this->create(Eatery::class);
        $closed = $this->build(Eatery::class)->closedDown()->create();

        $this->listEateries()
            ->filterTable('closed_down', true)
            ->assertCanSeeTableRecords([$closed])
            ->assertCanNotSeeTableRecords([$open]);
    }

    #[Test]
    public function itFiltersByType(): void
    {
        $eatery = $this->create(Eatery::class);
        $attraction = $this->build(Eatery::class)->attraction()->create();

        $this->listEateries()
            ->filterTable('type_id', 2)
            ->assertCanSeeTableRecords([$attraction])
            ->assertCanNotSeeTableRecords([$eatery]);
    }

    #[Test]
    public function itFiltersByReviewed(): void
    {
        $reviewed = $this->create(Eatery::class);
        $notReviewed = $this->create(Eatery::class);

        $this->create(EateryReview::class, ['wheretoeat_id' => $reviewed->id]);

        $this->listEateries()
            ->filterTable('reviewed', true)
            ->assertCanSeeTableRecords([$reviewed])
            ->assertCanNotSeeTableRecords([$notReviewed]);
    }

    #[Test]
    public function itShowsEverythingWhenTheReviewedFilterIsBlank(): void
    {
        $reviewed = $this->create(Eatery::class);
        $notReviewed = $this->create(Eatery::class);

        $this->create(EateryReview::class, ['wheretoeat_id' => $reviewed->id]);

        $this->listEateries()->assertCanSeeTableRecords([$reviewed, $notReviewed]);
    }

    #[Test]
    public function itSearchesByName(): void
    {
        $match = $this->create(Eatery::class, ['name' => 'The Gluten Free Cafe']);
        $other = $this->create(Eatery::class, ['name' => 'Somewhere Else']);

        $this->listEateries()
            ->searchTable('Gluten Free')
            ->assertCanSeeTableRecords([$match])
            ->assertCanNotSeeTableRecords([$other]);
    }

    #[Test]
    public function itSearchesById(): void
    {
        $match = $this->create(Eatery::class);
        $other = $this->create(Eatery::class);

        $this->listEateries()
            ->searchTable((string) $match->id)
            ->assertCanSeeTableRecords([$match])
            ->assertCanNotSeeTableRecords([$other]);
    }

    #[Test]
    public function itSearchesByTown(): void
    {
        $this->create(EateryCounty::class, ['id' => 2, 'county' => 'Cornwall', 'country_id' => 1]);
        $this->create(EateryTown::class, ['id' => 2, 'town' => 'Truro', 'county_id' => 2]);

        $match = $this->create(Eatery::class, ['county_id' => 2, 'town_id' => 2]);
        $other = $this->create(Eatery::class);

        $this->listEateries()
            ->searchTable('Truro')
            ->assertCanSeeTableRecords([$match])
            ->assertCanNotSeeTableRecords([$other]);
    }

    #[Test]
    public function itSearchesByCounty(): void
    {
        $this->create(EateryCounty::class, ['id' => 2, 'county' => 'Cornwall', 'country_id' => 1]);
        $this->create(EateryTown::class, ['id' => 2, 'town' => 'Truro', 'county_id' => 2]);

        $match = $this->create(Eatery::class, ['county_id' => 2, 'town_id' => 2]);
        $other = $this->create(Eatery::class);

        $this->listEateries()
            ->searchTable('Cornwall')
            ->assertCanSeeTableRecords([$match])
            ->assertCanNotSeeTableRecords([$other]);
    }

    #[Test]
    public function itSearchesByArea(): void
    {
        $area = $this->create(EateryArea::class, ['area' => 'Shoreditch', 'town_id' => 1]);

        $match = $this->create(Eatery::class, ['area_id' => $area->id]);
        $other = $this->create(Eatery::class);

        $this->listEateries()
            ->searchTable('Shoreditch')
            ->assertCanSeeTableRecords([$match])
            ->assertCanNotSeeTableRecords([$other]);
    }

    #[Test]
    public function itReplicatesAnEateryWithItsVisibilityTurnedOff(): void
    {
        $eatery = $this->create(Eatery::class, ['name' => 'The Gluten Free Cafe']);

        $this->listEateries()->callAction(TestAction::make('replicate')->table($eatery));

        $this->assertDatabaseCount(Eatery::class, 2);

        $replica = Eatery::query()->withoutGlobalScopes()->latest('id')->firstOrFail();

        $this->assertNotSame($eatery->id, $replica->id);
        $this->assertSame('The Gluten Free Cafe', $replica->name);
        $this->assertFalse($replica->live);
    }

    protected function listEateries(): Testable
    {
        return Livewire::test(ListEateries::class);
    }
}
