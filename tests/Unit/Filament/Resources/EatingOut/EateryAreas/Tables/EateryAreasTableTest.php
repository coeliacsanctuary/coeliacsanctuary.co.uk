<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryAreas\Tables;

use App\Filament\Resources\EatingOut\EateryAreas\EateryAreaResource;
use App\Filament\Resources\EatingOut\EateryAreas\Pages\ListEateryAreas;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
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

class EateryAreasTableTest extends TestCase
{
    protected EateryCounty $london;

    protected EateryTown $borough;

    protected EateryArea $area;

    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->london = $this->create(EateryCounty::class, ['county' => 'London', 'slug' => 'london', 'country_id' => 1]);
        $this->borough = $this->create(EateryTown::class, ['town' => 'Camden', 'slug' => 'camden', 'county_id' => $this->london->id]);
        $this->area = $this->create(EateryArea::class, ['area' => 'Holborn', 'slug' => 'holborn', 'latlng' => '51.5,-0.1', 'town_id' => $this->borough->id]);
    }

    #[Test]
    public function itListsTheColumnsInOrder(): void
    {
        $this->assertSame(
            ['area', 'town.town', 'latlng', 'image', 'live', 'eateries_count'],
            array_keys($this->listAreas()->instance()->getTable()->getColumns()),
        );
    }

    #[Test]
    public function itShowsTheBoroughEvenWithoutPlaces(): void
    {
        $this->listAreas()->assertTableColumnStateSet('town.town', 'Camden', $this->area);
    }

    #[Test]
    public function itSortsByNameByDefault(): void
    {
        $soho = $this->create(EateryArea::class, ['area' => 'Soho', 'town_id' => $this->borough->id]);
        $angel = $this->create(EateryArea::class, ['area' => 'Angel', 'town_id' => $this->borough->id]);

        $this->listAreas()->assertCanSeeTableRecords([$angel, $this->area, $soho], inOrder: true);
    }

    #[Test]
    public function itSearchesByName(): void
    {
        $soho = $this->create(EateryArea::class, ['area' => 'Soho', 'town_id' => $this->borough->id]);

        $this->listAreas()
            ->searchTable('Soh')
            ->assertCanSeeTableRecords([$soho])
            ->assertCanNotSeeTableRecords([$this->area]);
    }

    #[Test]
    public function itFiltersByBorough(): void
    {
        $barnet = $this->create(EateryTown::class, ['town' => 'Barnet', 'county_id' => $this->london->id]);
        $area = $this->create(EateryArea::class, ['town_id' => $barnet->id]);

        $this->listAreas()
            ->filterTable('town_id', $barnet->id)
            ->assertCanSeeTableRecords([$area])
            ->assertCanNotSeeTableRecords([$this->area]);
    }

    #[Test]
    public function itOffersBoroughsWithoutPlacesInTheFilter(): void
    {
        $this->assertSame(
            [$this->borough->id => 'Camden'],
            $this->listAreas()->instance()->getTable()->getFilter('town_id')->getOptions(),
        );
    }

    #[Test]
    public function itIsLiveWithOnlyALiveBranch(): void
    {
        $this->create(NationwideBranch::class, ['area_id' => $this->area->id, 'live' => true]);

        $this->listAreas()->assertTableColumnStateSet('live', true, $this->area);
    }

    #[Test]
    public function itIsNotLiveWithoutALiveEateryOrBranch(): void
    {
        $this->build(Eatery::class)->notLive()->create(['area_id' => $this->area->id]);
        $this->create(NationwideBranch::class, ['area_id' => $this->area->id, 'live' => false]);

        $this->listAreas()->assertTableColumnStateSet('live', false, $this->area);
    }

    #[Test]
    public function itOnlyCountsLiveEateries(): void
    {
        $this->create(Eatery::class, 2, ['area_id' => $this->area->id, 'live' => true]);
        $this->build(Eatery::class)->notLive()->create(['area_id' => $this->area->id]);

        $this->listAreas()
            ->assertTableColumnStateSet('eateries_count', 2, $this->area)
            ->assertTableColumnStateSet('live', true, $this->area);
    }

    #[Test]
    public function itOpensTheEditPageWhenARowIsClicked(): void
    {
        $this->assertSame(
            EateryAreaResource::getUrl('edit', ['record' => $this->area]),
            $this->listAreas()->instance()->getTable()->getRecordUrl($this->area),
        );
    }

    #[Test]
    public function itCanEditButNotDelete(): void
    {
        $this->listAreas()
            ->assertActionVisible(TestAction::make('edit')->table($this->area))
            ->assertActionDoesNotExist(TestAction::make('delete')->table($this->area));
    }

    protected function listAreas(): Testable
    {
        return Livewire::test(ListEateryAreas::class);
    }
}
