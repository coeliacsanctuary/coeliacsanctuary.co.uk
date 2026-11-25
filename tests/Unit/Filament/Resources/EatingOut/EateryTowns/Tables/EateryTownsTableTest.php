<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryTowns\Tables;

use App\Filament\Resources\EatingOut\EateryBoroughs\EateryBoroughResource;
use App\Filament\Resources\EatingOut\EateryBoroughs\Pages\ListEateryBoroughs;
use App\Filament\Resources\EatingOut\EateryTowns\EateryTownResource;
use App\Filament\Resources\EatingOut\EateryTowns\Pages\ListEateryTowns;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Bus;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryTownsTableTest extends TestCase
{
    protected EateryCounty $london;

    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->london = $this->create(EateryCounty::class, ['county' => 'London', 'slug' => 'london']);
    }

    #[Test]
    public function itListsTheColumnsInOrder(): void
    {
        $this->assertSame(
            ['town', 'county.county', 'latlng', 'image', 'live', 'eateries_count'],
            array_keys($this->listTowns()->instance()->getTable()->getColumns()),
        );
    }

    #[Test]
    public function itDoesNotShowTheCountyColumnOrFilterForBoroughs(): void
    {
        $table = Livewire::test(ListEateryBoroughs::class)->instance()->getTable();

        $this->assertSame(['town', 'latlng', 'image', 'live', 'eateries_count'], array_keys($table->getColumns()));
        $this->assertEmpty($table->getFilters());
    }

    #[Test]
    public function itSeparatesTownsAndBoroughs(): void
    {
        $borough = $this->create(EateryTown::class, ['county_id' => $this->london->id]);

        $this->listTowns()->assertCanSeeTableRecords([$this->town()])->assertCanNotSeeTableRecords([$borough]);

        Livewire::test(ListEateryBoroughs::class)->assertCanSeeTableRecords([$borough])->assertCanNotSeeTableRecords([$this->town()]);
    }

    #[Test]
    public function itHidesNationwide(): void
    {
        $nationwide = $this->create(EateryTown::class, ['slug' => 'nationwide', 'county_id' => 1]);

        $this->listTowns()->assertCanNotSeeTableRecords([$nationwide]);
    }

    #[Test]
    public function itShowsTheCountryAndCountyEvenWithoutPlaces(): void
    {
        $this->listTowns()->assertTableColumnFormattedStateSet('county.county', 'England - Cheshire', $this->town());
    }

    #[Test]
    public function itSortsByNameByDefault(): void
    {
        $york = $this->create(EateryTown::class, ['town' => 'York', 'county_id' => 1]);
        $bath = $this->create(EateryTown::class, ['town' => 'Bath', 'county_id' => 1]);

        $this->listTowns()->assertCanSeeTableRecords([$bath, $this->town(), $york], inOrder: true);
    }

    #[Test]
    public function itSearchesByName(): void
    {
        $bath = $this->create(EateryTown::class, ['town' => 'Bath', 'county_id' => 1]);

        $this->listTowns()
            ->searchTable('Bat')
            ->assertCanSeeTableRecords([$bath])
            ->assertCanNotSeeTableRecords([$this->town()]);
    }

    #[Test]
    public function itFiltersByCounty(): void
    {
        $county = $this->create(EateryCounty::class);
        $town = $this->create(EateryTown::class, ['county_id' => $county->id]);

        $this->listTowns()
            ->filterTable('county_id', $county->id)
            ->assertCanSeeTableRecords([$town])
            ->assertCanNotSeeTableRecords([$this->town()]);
    }

    #[Test]
    public function itIsLiveWithOnlyALiveBranch(): void
    {
        $this->create(NationwideBranch::class, ['town_id' => 1, 'live' => true]);

        $this->listTowns()->assertTableColumnStateSet('live', true, $this->town());
    }

    #[Test]
    public function itIsNotLiveWithoutALiveEateryOrBranch(): void
    {
        $this->build(Eatery::class)->notLive()->create();
        $this->create(NationwideBranch::class, ['town_id' => 1, 'live' => false]);

        $this->listTowns()->assertTableColumnStateSet('live', false, $this->town());
    }

    #[Test]
    public function itOnlyCountsLiveEateries(): void
    {
        $this->create(Eatery::class, 2, ['live' => true]);
        $this->build(Eatery::class)->notLive()->create();

        $this->listTowns()
            ->assertTableColumnStateSet('eateries_count', 2, $this->town())
            ->assertTableColumnStateSet('live', true, $this->town());
    }

    #[Test]
    public function itOpensTheEditPageWhenARowIsClicked(): void
    {
        $borough = $this->create(EateryTown::class, ['county_id' => $this->london->id]);

        $this->assertSame(
            EateryTownResource::getUrl('edit', ['record' => $this->town()]),
            $this->listTowns()->instance()->getTable()->getRecordUrl($this->town()),
        );

        $this->assertSame(
            EateryBoroughResource::getUrl('edit', ['record' => $borough]),
            Livewire::test(ListEateryBoroughs::class)->instance()->getTable()->getRecordUrl($borough),
        );
    }

    #[Test]
    public function itCanEditButNotDelete(): void
    {
        $this->listTowns()
            ->assertActionVisible(TestAction::make('edit')->table($this->town()))
            ->assertActionDoesNotExist(TestAction::make('delete')->table($this->town()));
    }

    protected function town(): EateryTown
    {
        return EateryTown::query()->withoutGlobalScopes()->findOrFail(1);
    }

    protected function listTowns(): Testable
    {
        return Livewire::test(ListEateryTowns::class);
    }
}
