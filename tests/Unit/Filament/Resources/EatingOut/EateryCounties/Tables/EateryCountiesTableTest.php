<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryCounties\Tables;

use App\Filament\Resources\EatingOut\EateryCounties\EateryCountyResource;
use App\Filament\Resources\EatingOut\EateryCounties\Pages\ListEateryCounties;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\NationwideBranch;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Bus;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryCountiesTableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itHidesNationwide(): void
    {
        $nationwide = $this->create(EateryCounty::class, ['county' => 'Nationwide', 'slug' => 'nationwide']);

        $this->listCounties()
            ->assertCanSeeTableRecords([$this->county()])
            ->assertCanNotSeeTableRecords([$nationwide]);
    }

    #[Test]
    public function itListsTheColumnsInOrder(): void
    {
        $this->assertSame(
            ['county', 'country.country', 'latlng', 'image', 'live', 'towns_count', 'eateries_count'],
            array_keys($this->listCounties()->instance()->getTable()->getColumns()),
        );
    }

    #[Test]
    public function itSortsByNameByDefault(): void
    {
        $york = $this->create(EateryCounty::class, ['county' => 'Yorkshire']);
        $avon = $this->create(EateryCounty::class, ['county' => 'Avon']);

        $this->listCounties()->assertCanSeeTableRecords([$avon, $this->county(), $york], inOrder: true);
    }

    #[Test]
    #[DataProvider('sortableColumns')]
    public function itCanSortByAColumn(string $column): void
    {
        $this->assertTrue($this->listCounties()->instance()->getTable()->getColumn($column)->isSortable());
    }

    public static function sortableColumns(): array
    {
        return [
            'name' => ['county'],
            'towns' => ['towns_count'],
            'eateries' => ['eateries_count'],
        ];
    }

    #[Test]
    public function itSearchesByCountyButNotCountry(): void
    {
        $county = $this->create(EateryCounty::class, ['county' => 'Cornwall']);

        $this->listCounties()
            ->searchTable('Cornw')
            ->assertCanSeeTableRecords([$county])
            ->assertCanNotSeeTableRecords([$this->county()])
            ->searchTable('England')
            ->assertCanNotSeeTableRecords([$county, $this->county()]);
    }

    #[Test]
    public function itFiltersByCountry(): void
    {
        $this->create(EateryCountry::class, ['id' => 2, 'country' => 'Wales']);

        $county = $this->create(EateryCounty::class, ['country_id' => 2]);

        $this->listCounties()
            ->filterTable('country', 2)
            ->assertCanSeeTableRecords([$county])
            ->assertCanNotSeeTableRecords([$this->county()]);
    }

    #[Test]
    public function itIsLiveWithALiveEatery(): void
    {
        $this->create(Eatery::class, ['live' => true]);

        $this->listCounties()->assertTableColumnStateSet('live', true, $this->county());
    }

    #[Test]
    public function itIsLiveWithOnlyALiveBranch(): void
    {
        $this->create(NationwideBranch::class, ['county_id' => 1, 'live' => true]);

        $this->listCounties()->assertTableColumnStateSet('live', true, $this->county());
    }

    #[Test]
    public function itIsNotLiveWithoutALiveEateryOrBranch(): void
    {
        $this->build(Eatery::class)->notLive()->create();
        $this->create(NationwideBranch::class, ['county_id' => 1, 'live' => false]);

        $this->listCounties()->assertTableColumnStateSet('live', false, $this->county());
    }

    #[Test]
    public function itOnlyCountsLiveEateries(): void
    {
        $this->create(Eatery::class, 2, ['live' => true]);
        $this->build(Eatery::class)->notLive()->create();

        $this->listCounties()->assertTableColumnStateSet('eateries_count', 2, $this->county());
    }

    #[Test]
    public function itOpensTheEditPageWhenARowIsClicked(): void
    {
        $this->assertSame(
            EateryCountyResource::getUrl('edit', ['record' => $this->county()]),
            $this->listCounties()->instance()->getTable()->getRecordUrl($this->county()),
        );
    }

    #[Test]
    public function itCanViewAndEditButNotDelete(): void
    {
        $this->listCounties()
            ->assertActionVisible(TestAction::make('view')->table($this->county()))
            ->assertActionVisible(TestAction::make('edit')->table($this->county()))
            ->assertActionDoesNotExist(TestAction::make('delete')->table($this->county()));
    }

    protected function county(): EateryCounty
    {
        return EateryCounty::query()->withoutGlobalScopes()->findOrFail(1);
    }

    protected function listCounties(): Testable
    {
        return Livewire::test(ListEateryCounties::class);
    }
}
