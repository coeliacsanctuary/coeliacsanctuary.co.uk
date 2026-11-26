<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryMagicRoutes\Tables;

use App\Filament\Resources\EatingOut\EateryMagicRoutes\EateryMagicRouteResource;
use App\Filament\Resources\EatingOut\EateryMagicRoutes\Pages\ListEateryMagicRoutes;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryMagicRouteRecord;
use App\Models\EatingOut\EateryTown;
use App\Models\User;
use App\Pipelines\EatingOut\GetEateries\GetEateriesForMagicRoutePipeline;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Bus;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryMagicRoutesTableTest extends TestCase
{
    protected EateryMagicRouteRecord $countyRoute;

    protected EateryMagicRouteRecord $townRoute;

    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->create(Eatery::class, ['county_id' => 1, 'town_id' => 1, 'live' => true]);

        $this->countyRoute = $this->routeRecordFor(EateryCounty::query()->first());
        $this->townRoute = $this->routeRecordFor(EateryTown::query()->first());

        $this->mock(GetEateriesForMagicRoutePipeline::class)
            ->shouldReceive('run')
            ->andReturn(collect([new Eatery(), new Eatery()]));
    }

    #[Test]
    public function itListsTheColumnsInOrder(): void
    {
        $this->assertSame(
            ['id', 'resolver_type', 'location', 'eateries'],
            array_keys($this->listMagicRoutes()->instance()->getTable()->getColumns()),
        );
    }

    #[Test]
    public function itListsTheMagicRoutesById(): void
    {
        $this->listMagicRoutes()->assertCanSeeTableRecords([$this->countyRoute, $this->townRoute], inOrder: true);
    }

    #[Test]
    public function itLabelsCountiesWithTheirCountryAndTownsByName(): void
    {
        $this->listMagicRoutes()
            ->assertTableColumnStateSet('location', 'England - Cheshire', $this->countyRoute)
            ->assertTableColumnStateSet('location', 'Crewe', $this->townRoute);
    }

    #[Test]
    public function itCountsTheEateriesForEachRoute(): void
    {
        $this->listMagicRoutes()->assertTableColumnStateSet('eateries', 2, $this->countyRoute);
    }

    #[Test]
    public function itLinksEachRowToTheViewPage(): void
    {
        $this->assertSame(
            EateryMagicRouteResource::getUrl('view', ['record' => $this->countyRoute]),
            $this->listMagicRoutes()->instance()->getTable()->getRecordUrl($this->countyRoute),
        );
    }

    protected function routeRecordFor(EateryCounty|EateryTown $location): EateryMagicRouteRecord
    {
        return $this->create(EateryMagicRouteRecord::class, [
            'raw_location' => $location->slug,
            'location_type' => $location::class,
            'location_id' => $location->id,
        ]);
    }

    protected function listMagicRoutes(): Testable
    {
        return Livewire::test(ListEateryMagicRoutes::class);
    }
}
