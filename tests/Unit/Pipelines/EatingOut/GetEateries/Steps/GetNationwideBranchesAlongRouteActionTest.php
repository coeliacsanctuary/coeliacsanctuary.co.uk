<?php

declare(strict_types=1);

namespace Tests\Unit\Pipelines\EatingOut\GetEateries\Steps;

use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\LatLng;
use App\DataObjects\EatingOut\PendingEatery;
use App\DataObjects\EatingOut\TripRoute;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryType;
use App\Models\EatingOut\EateryVenueType;
use App\Models\EatingOut\NationwideBranch;
use App\Pipelines\EatingOut\GetEateries\Steps\GetNationwideBranchesAlongRouteAction;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class GetNationwideBranchesAlongRouteActionTest extends TestCase
{
    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->eatery = $this->create(Eatery::class, ['lat' => 51.5, 'lng' => -0.1, 'venue_type_id' => 1]);
    }

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

    protected function createBranch(float $lat, float $lng, ?Eatery $eatery = null): NationwideBranch
    {
        return $this->create(NationwideBranch::class, [
            'wheretoeat_id' => ($eatery ?? $this->eatery)->id,
            'lat' => $lat,
            'lng' => $lng,
        ]);
    }

    protected function callStep(array $filters = [], ?Collection $eateries = null, bool $withRoute = true): GetEateriesPipelineData
    {
        $pipelineData = new GetEateriesPipelineData(
            filters: $filters,
            eateries: $eateries,
            route: $withRoute ? $this->makeRoute() : null,
            routeRadius: $withRoute ? 1 : null,
        );

        $toReturn = null;

        $this->callAction(GetNationwideBranchesAlongRouteAction::class, $pipelineData, function (GetEateriesPipelineData $data) use (&$toReturn): void {
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
    public function itReturnsBranchesWithinTheRadiusOfTheRoute(): void
    {
        $near = $this->createBranch(53.1, -2.45);
        $this->createBranch(53.1, -2.6);

        $eateries = $this->callStep()->eateries;

        $this->assertSame([$near->id], $eateries->pluck('branchId')->all());
    }

    #[Test]
    public function itSetsTheRoutePositionOnThePendingEatery(): void
    {
        $branch = $this->createBranch(53.1, -2.45);

        /** @var PendingEatery $pendingEatery */
        $pendingEatery = $this->callStep()->eateries->sole();

        $this->assertSame($this->eatery->id, $pendingEatery->id);
        $this->assertSame($branch->id, $pendingEatery->branchId);
        $this->assertEqualsWithDelta(11120, $pendingEatery->ordering, 5);
        $this->assertEqualsWithDelta(668, $pendingEatery->distance, 1);
        $this->assertEqualsWithDelta(600, $pendingEatery->durationAlongRoute, 1);
        $this->assertSame(EateryType::EATERY, $pendingEatery->typeId);
        $this->assertSame(1, $pendingEatery->venueTypeId);
    }

    #[Test]
    public function itAppendsToThePassedInCollection(): void
    {
        $this->createBranch(53.1, -2.45);

        $eateries = $this->callStep(eateries: new Collection(['existing']))->eateries;

        $this->assertCount(2, $eateries);
        $this->assertSame('existing', $eateries->first());
    }

    #[Test]
    public function itDoesNotReturnBranchesOfEateriesThatAreClosedDown(): void
    {
        $closedDown = $this->build(Eatery::class)->closedDown()->create(['lat' => 51.5, 'lng' => -0.1]);

        $this->createBranch(53.1, -2.45, $closedDown);

        $this->assertCount(0, $this->callStep()->eateries);
    }

    #[Test]
    public function itCanFilterTheBranchesByCategory(): void
    {
        $attraction = $this->build(Eatery::class)->attraction()->create(['lat' => 51.5, 'lng' => -0.1]);

        $this->createBranch(53.1, -2.45);
        $branch = $this->createBranch(53.1, -2.45, $attraction);

        $eateries = $this->callStep(filters: ['categories' => ['att']])->eateries;

        $this->assertSame([$branch->id], $eateries->pluck('branchId')->all());
    }

    #[Test]
    public function itCanFilterTheBranchesByVenueType(): void
    {
        $venueType = $this->create(EateryVenueType::class, ['slug' => 'test']);
        $eatery = $this->create(Eatery::class, ['lat' => 51.5, 'lng' => -0.1, 'venue_type_id' => $venueType->id]);

        $this->createBranch(53.1, -2.45);
        $branch = $this->createBranch(53.1, -2.45, $eatery);

        $eateries = $this->callStep(filters: ['venueTypes' => ['test']])->eateries;

        $this->assertSame([$branch->id], $eateries->pluck('branchId')->all());
    }

    #[Test]
    public function itCanFilterTheBranchesByFeature(): void
    {
        $feature = $this->create(EateryFeature::class, ['slug' => 'test']);
        $eatery = $this->create(Eatery::class, ['lat' => 51.5, 'lng' => -0.1]);

        $feature->eateries()->attach($eatery);

        $this->createBranch(53.1, -2.45);
        $branch = $this->createBranch(53.1, -2.45, $eatery);

        $eateries = $this->callStep(filters: ['features' => ['test']])->eateries;

        $this->assertSame([$branch->id], $eateries->pluck('branchId')->all());
    }
}
