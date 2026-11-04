<?php

declare(strict_types=1);

namespace Tests\Unit\Pipelines\EatingOut\GetEateries\Steps;

use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\LatLng;
use App\DataObjects\EatingOut\PendingEatery;
use App\DataObjects\EatingOut\TripRoute;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryVenueType;
use App\Pipelines\EatingOut\GetEateries\Steps\GetEateriesAlongRouteAction;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class GetEateriesAlongRouteActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);
    }

    /** A route running due north from 53.00,-2.44 to 53.20,-2.44, roughly 22.2km, taking 20 minutes. */
    protected function makeRoute(): TripRoute
    {
        return new TripRoute(
            geometry: '',
            points: collect(range(0, 20))->map(fn (int $index): LatLng => new LatLng(53 + ($index / 100), -2.44)),
            distance: 22239,
            duration: 1200,
            timings: [['from' => 0, 'to' => 20, 'distance' => 22239, 'duration' => 1200]],
        );
    }

    protected function callStep(array $filters = [], ?TripRoute $route = null, ?Collection $eateries = null, bool $withRoute = true): GetEateriesPipelineData
    {
        $pipelineData = new GetEateriesPipelineData(
            filters: $filters,
            eateries: $eateries,
            route: $withRoute ? ($route ?? $this->makeRoute()) : null,
            routeRadius: $withRoute ? 1 : null,
        );

        $toReturn = null;

        $this->callAction(GetEateriesAlongRouteAction::class, $pipelineData, function (GetEateriesPipelineData $data) use (&$toReturn): void {
            $toReturn = $data;
        });

        return $toReturn;
    }

    #[Test]
    public function itThrowsWithoutARoute(): void
    {
        $this->expectException(RuntimeException::class);

        $this->callStep(withRoute: false);
    }

    #[Test]
    public function itReturnsEateriesWithinTheRadiusOfTheRoute(): void
    {
        $near = $this->create(Eatery::class, ['lat' => 53.1, 'lng' => -2.45]);
        $this->create(Eatery::class, ['lat' => 53.1, 'lng' => -2.6]);

        $eateries = $this->callStep()->eateries;

        $this->assertSame([$near->id], $eateries->pluck('id')->all());
    }

    #[Test]
    public function itDoesNotReturnEateriesBeyondEitherEndOfTheRoute(): void
    {
        $this->create(Eatery::class, ['lat' => 52.9, 'lng' => -2.44]);
        $this->create(Eatery::class, ['lat' => 53.3, 'lng' => -2.44]);

        $this->assertCount(0, $this->callStep()->eateries);
    }

    #[Test]
    public function itSetsTheRoutePositionOnThePendingEatery(): void
    {
        $eatery = $this->create(Eatery::class, ['lat' => 53.1, 'lng' => -2.45, 'type_id' => 1, 'venue_type_id' => 1]);

        /** @var PendingEatery $pendingEatery */
        $pendingEatery = $this->callStep()->eateries->sole();

        $this->assertSame($eatery->id, $pendingEatery->id);
        $this->assertNull($pendingEatery->branchId);
        $this->assertEqualsWithDelta(11120, $pendingEatery->ordering, 5);
        $this->assertEqualsWithDelta(668, $pendingEatery->distance, 1);
        $this->assertEqualsWithDelta(600, $pendingEatery->durationAlongRoute, 1);
        $this->assertSame(1, $pendingEatery->typeId);
        $this->assertSame(1, $pendingEatery->venueTypeId);
    }

    #[Test]
    public function itAppendsToThePassedInCollection(): void
    {
        $this->create(Eatery::class, ['lat' => 53.1, 'lng' => -2.45]);

        $eateries = $this->callStep(eateries: new Collection(['existing']))->eateries;

        $this->assertCount(2, $eateries);
        $this->assertSame('existing', $eateries->first());
    }

    #[Test]
    public function itDoesNotReturnEateriesThatAreClosedDown(): void
    {
        $this->build(Eatery::class)->closedDown()->create(['lat' => 53.1, 'lng' => -2.45]);

        $this->assertCount(0, $this->callStep()->eateries);
    }

    #[Test]
    public function itDoesNotReturnNationwideEateries(): void
    {
        $nationwide = $this->create(EateryCounty::class, ['county' => 'Nationwide']);

        $this->create(Eatery::class, ['lat' => 53.1, 'lng' => -2.45, 'county_id' => $nationwide->id]);

        $this->assertCount(0, $this->callStep()->eateries);
    }

    #[Test]
    public function itCanFilterTheEateriesByCategory(): void
    {
        $this->create(Eatery::class, ['lat' => 53.1, 'lng' => -2.45]);
        $attraction = $this->build(Eatery::class)->attraction()->create(['lat' => 53.1, 'lng' => -2.45]);

        $eateries = $this->callStep(filters: ['categories' => ['att']])->eateries;

        $this->assertSame([$attraction->id], $eateries->pluck('id')->all());
    }

    #[Test]
    public function itCanFilterTheEateriesByVenueType(): void
    {
        $venueType = $this->create(EateryVenueType::class, ['slug' => 'test']);

        $this->create(Eatery::class, ['lat' => 53.1, 'lng' => -2.45]);
        $eatery = $this->create(Eatery::class, ['lat' => 53.1, 'lng' => -2.45, 'venue_type_id' => $venueType->id]);

        $eateries = $this->callStep(filters: ['venueTypes' => ['test']])->eateries;

        $this->assertSame([$eatery->id], $eateries->pluck('id')->all());
    }

    #[Test]
    public function itCanFilterTheEateriesByFeature(): void
    {
        $feature = $this->create(EateryFeature::class, ['slug' => 'test']);

        $this->create(Eatery::class, ['lat' => 53.1, 'lng' => -2.45]);
        $eatery = $this->create(Eatery::class, ['lat' => 53.1, 'lng' => -2.45]);

        $feature->eateries()->attach($eatery);

        $eateries = $this->callStep(filters: ['features' => ['test']])->eateries;

        $this->assertSame([$eatery->id], $eateries->pluck('id')->all());
    }
}
