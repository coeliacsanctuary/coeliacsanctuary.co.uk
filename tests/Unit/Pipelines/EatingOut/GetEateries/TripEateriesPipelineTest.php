<?php

declare(strict_types=1);

namespace Tests\Unit\Pipelines\EatingOut\GetEateries;

use App\DataObjects\EatingOut\LatLng;
use App\DataObjects\EatingOut\TripRoute;
use App\Models\EatingOut\Eatery;
use App\Pipelines\EatingOut\GetEateries\Steps\GetEateriesAlongRouteAction;
use App\Pipelines\EatingOut\GetEateries\Steps\GetNationwideBranchesAlongRouteAction;
use App\Pipelines\EatingOut\GetEateries\Steps\SerialiseBrowseResultsAction;
use App\Pipelines\EatingOut\GetEateries\Steps\SortPendingEateriesAction;
use App\Pipelines\EatingOut\GetEateries\TripEateriesPipeline;
use App\Resources\EatingOut\EateryTripResource;
use Database\Seeders\EateryScaffoldingSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TripEateriesPipelineTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);
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

    #[Test]
    public function itCallsTheActions(): void
    {
        $this->expectPipelineToExecute(GetEateriesAlongRouteAction::class);
        $this->expectPipelineToExecute(GetNationwideBranchesAlongRouteAction::class);
        $this->expectPipelineToExecute(SortPendingEateriesAction::class);
        $this->expectPipelineToExecute(SerialiseBrowseResultsAction::class);

        $this->runPipeline(TripEateriesPipeline::class, $this->makeRoute(), 1, []);
    }

    #[Test]
    public function itReturnsTheEateriesInJourneyOrder(): void
    {
        $last = $this->create(Eatery::class, ['lat' => 53.18, 'lng' => -2.45]);
        $first = $this->create(Eatery::class, ['lat' => 53.02, 'lng' => -2.45]);
        $middle = $this->create(Eatery::class, ['lat' => 53.1, 'lng' => -2.43]);

        $results = $this->runPipeline(TripEateriesPipeline::class, $this->makeRoute(), 1, [], EateryTripResource::class);

        $this->assertSame([$first->id, $middle->id, $last->id], $results->map(fn (EateryTripResource $resource): int => $resource->resource->id)->all());
    }
}
