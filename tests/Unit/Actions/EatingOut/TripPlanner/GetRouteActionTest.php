<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut\TripPlanner;

use Illuminate\Support\Collection;
use App\Actions\EatingOut\TripPlanner\GetRouteAction;
use App\DataObjects\EatingOut\LatLng;
use App\DataObjects\EatingOut\TripRoute;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetRouteActionTest extends TestCase
{
    protected function fakeDirectionsResponse(): void
    {
        Http::preventStrayRequests();

        Http::fake([
            'api.heigit.org/openrouteservice/v2/directions/driving-car' => Http::response([
                'routes' => [[
                    'summary' => ['distance' => 1500.5, 'duration' => 120.5],
                    'geometry' => '_p~iF~ps|U_ulLnnqC_mqNvxq`@',
                    'segments' => [
                        ['steps' => [
                            ['distance' => 1000, 'duration' => 80, 'type' => 11, 'instruction' => 'Head north', 'name' => '-', 'way_points' => [0, 1]],
                        ]],
                        ['steps' => [
                            ['distance' => 500.5, 'duration' => 40.5, 'type' => 10, 'instruction' => 'Arrive', 'name' => '-', 'way_points' => [1, 2]],
                        ]],
                    ],
                ]],
            ]),
        ]);
    }

    /** @return Collection<int, LatLng> */
    protected function points(): Collection
    {
        return collect([new LatLng(53.1, -2.44), new LatLng(53.3, -2.3), new LatLng(53.48, -2.24)]);
    }

    #[Test]
    public function itSendsThePointsInOrderAsLngLatPairs(): void
    {
        $this->fakeDirectionsResponse();

        $this->callAction(GetRouteAction::class, $this->points());

        Http::assertSent(fn (Request $request): bool => $request['coordinates'] === [[-2.44, 53.1], [-2.3, 53.3], [-2.24, 53.48]]
            && $request->hasHeader('Authorization', config('services.openRouteService.key')));
    }

    #[Test]
    public function itReturnsTheRouteTotalsAndGeometry(): void
    {
        $this->fakeDirectionsResponse();

        $route = $this->callAction(GetRouteAction::class, $this->points());

        $this->assertInstanceOf(TripRoute::class, $route);
        $this->assertSame('_p~iF~ps|U_ulLnnqC_mqNvxq`@', $route->geometry);
        $this->assertSame(1500.5, $route->distance);
        $this->assertSame(120.5, $route->duration);
    }

    #[Test]
    public function itDecodesTheGeometryIntoPoints(): void
    {
        $this->fakeDirectionsResponse();

        $route = $this->callAction(GetRouteAction::class, $this->points());

        $this->assertSame(
            [[38.5, -120.2], [40.7, -120.95], [43.252, -126.453]],
            $route->points->map(fn (LatLng $point): array => [$point->lat, $point->lng])->all(),
        );
    }

    #[Test]
    public function itFlattensTheStepsFromEverySegmentIntoTimings(): void
    {
        $this->fakeDirectionsResponse();

        $route = $this->callAction(GetRouteAction::class, $this->points());

        $this->assertSame([
            ['from' => 0, 'to' => 1, 'distance' => 1000.0, 'duration' => 80.0],
            ['from' => 1, 'to' => 2, 'distance' => 500.5, 'duration' => 40.5],
        ], $route->timings);
    }

    #[Test]
    public function itThrowsWhenOpenRouteServiceFails(): void
    {
        Http::preventStrayRequests();

        Http::fake([
            'api.heigit.org/openrouteservice/v2/directions/driving-car' => Http::response(['error' => 'bad'], 500),
        ]);

        $this->expectException(RequestException::class);

        $this->callAction(GetRouteAction::class, $this->points());
    }
}
