<?php

declare(strict_types=1);

namespace Tests\Unit\DataObjects\EatingOut;

use App\DataObjects\EatingOut\LatLng;
use App\DataObjects\EatingOut\TripRoute;
use App\DataObjects\EatingOut\TripRouteChunk;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TripRouteTest extends TestCase
{
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
    public function itSplitsTheRouteIntoChunksOfTheGivenLength(): void
    {
        $chunks = $this->makeRoute()->chunks(padding: 0, chunkLength: 5000);

        $this->assertSame(
            [[53.0, 53.05], [53.05, 53.1], [53.1, 53.15], [53.15, 53.2]],
            $chunks->map(fn (TripRouteChunk $chunk): array => [$chunk->points[0]['lat'], $chunk->points[count($chunk->points) - 1]['lat']])->all(),
        );
    }

    #[Test]
    public function eachChunkStartsWhereThePreviousOneEnded(): void
    {
        $chunks = $this->makeRoute()->chunks(padding: 0, chunkLength: 5000)->values();

        $chunks->slice(1)->each(function (TripRouteChunk $chunk, int $index) use ($chunks): void {
            $previous = $chunks[$index - 1]->points;

            $this->assertSame($previous[count($previous) - 1], $chunk->points[0]);
        });
    }

    #[Test]
    public function itPadsEachChunksBoundsByTheGivenDistance(): void
    {
        $chunk = $this->makeRoute()->chunks(padding: 1113.2, chunkLength: 50000)->first();

        $this->assertEqualsWithDelta(52.99, $chunk->latBounds['min'], 0.0001);
        $this->assertEqualsWithDelta(53.21, $chunk->latBounds['max'], 0.0001);
        $this->assertEqualsWithDelta(-2.4566, $chunk->lngBounds['min'], 0.0001);
        $this->assertEqualsWithDelta(-2.4234, $chunk->lngBounds['max'], 0.0001);
    }

    #[Test]
    public function itLocatesAPointAgainstTheRoute(): void
    {
        $route = $this->makeRoute();

        $location = $route->locate($route->chunks(padding: 2000), 53.1, -2.45);

        $this->assertEqualsWithDelta(668, $location['distance'], 1);
        $this->assertEqualsWithDelta(11120, $location['along'], 5);
        $this->assertEqualsWithDelta(600, $location['duration'], 1);
    }

    #[Test]
    public function itSpreadsAStepsDurationAcrossItsLength(): void
    {
        $route = new TripRoute(
            geometry: '',
            points: collect(range(0, 20))->map(fn (int $index): LatLng => new LatLng(53 + ($index / 100), -2.44)),
            distance: 22239,
            duration: 1500,
            timings: [
                ['from' => 0, 'to' => 10, 'distance' => 11120, 'duration' => 1200],
                ['from' => 10, 'to' => 20, 'distance' => 11120, 'duration' => 300],
            ],
        );

        $chunks = $route->chunks(padding: 2000);

        $this->assertEqualsWithDelta(600, $route->locate($chunks, 53.05, -2.44)['duration'], 1);
        $this->assertEqualsWithDelta(1350, $route->locate($chunks, 53.15, -2.44)['duration'], 1);
    }

    #[Test]
    public function itReturnsAnInfiniteDistanceForAPointOutsideEveryChunk(): void
    {
        $route = $this->makeRoute();

        $this->assertSame(INF, $route->locate($route->chunks(padding: 1000), 54.0, -2.44)['distance']);
    }

    #[Test]
    public function itGivesTheStartOfTheRouteForAPointAtTheBeginning(): void
    {
        $point = $this->makeRoute()->pointAtDuration(0);

        $this->assertSame([53.0, -2.44, 0.0], [$point['lat'], $point['lng'], $point['along']]);
    }

    #[Test]
    public function itGivesTheEndOfTheRouteForAPointAtOrBeyondTheFullDuration(): void
    {
        $route = $this->makeRoute();

        $this->assertEqualsWithDelta(53.2, $route->pointAtDuration(1200)['lat'], 0.0001);
        $this->assertEqualsWithDelta(53.2, $route->pointAtDuration(5000)['lat'], 0.0001);
    }

    #[Test]
    public function itInterpolatesAPointPartWayAlongTheRoute(): void
    {
        $point = $this->makeRoute()->pointAtDuration(630);

        $this->assertEqualsWithDelta(53.105, $point['lat'], 0.0001);
        $this->assertSame(-2.44, $point['lng']);
        $this->assertEqualsWithDelta(11675, $point['along'], 5);
        $this->assertSame(630.0, $point['duration']);
    }

    #[Test]
    public function itLocatesPointsInOrderOnARouteThatDoublesBack(): void
    {
        $route = new TripRoute(
            geometry: '',
            points: collect([...range(0, 20), ...range(19, 0)])->map(fn (int $index): LatLng => new LatLng(53 + ($index / 100), -2.44)),
            distance: 44478,
            duration: 2400,
            timings: [['from' => 0, 'to' => 40, 'distance' => 44478, 'duration' => 2400]],
        );

        $located = $route->locatePoints([['lat' => 53.2, 'lng' => -2.44], ['lat' => 53.1, 'lng' => -2.44]]);

        $this->assertEqualsWithDelta([1200, 1800], array_column($located, 'duration'), 0.01);
    }
}
