<?php

declare(strict_types=1);

namespace App\Actions\EatingOut\TripPlanner;

use App\DataObjects\EatingOut\LatLng;
use App\DataObjects\EatingOut\TripRoute;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class GetRouteAction
{
    public function __construct(protected DecodePolylineAction $decodePolylineAction)
    {
        //
    }

    /** @param  Collection<int, LatLng>  $points */
    public function handle(Collection $points): TripRoute
    {
        $response = Http::openRouteService()
            ->post('v2/directions/driving-car', [
                'coordinates' => $points->map(fn (LatLng $point): array => [$point->lng, $point->lat])->all(),
            ])
            ->throw();

        /** @var array{geometry: string, summary: array{distance: float, duration: float}, segments: array<int, array{steps: array<int, array{way_points: array{int, int}, distance: float, duration: float}>}>} $route */
        $route = $response->json('routes.0');

        $timings = collect($route['segments'])
            ->flatMap(fn (array $segment): array => $segment['steps'])
            ->map(fn (array $step): array => [
                'from' => $step['way_points'][0],
                'to' => $step['way_points'][1],
                'distance' => (float) $step['distance'],
                'duration' => (float) $step['duration'],
            ])
            ->values()
            ->all();

        return new TripRoute(
            geometry: $route['geometry'],
            points: $this->decodePolylineAction->handle($route['geometry']),
            distance: (float) $route['summary']['distance'],
            duration: (float) $route['summary']['duration'],
            timings: $timings,
        );
    }
}
