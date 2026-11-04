<?php

declare(strict_types=1);

namespace App\Actions\EatingOut\TripPlanner;

use App\DataObjects\EatingOut\LatLng;
use App\Models\EatingOut\EateryTrip;

class UpdateTripAction
{
    /** @param  array<int, array{label: string, lat: float, lng: float}>  $points */
    public function handle(EateryTrip $trip, array $points, int $distance): EateryTrip
    {
        $route = app(GetRouteAction::class)->handle(
            collect($points)->map(fn (array $point): LatLng => new LatLng($point['lat'], $point['lng']))->values(),
        );

        $trip->update([
            'points' => $points,
            'distance' => $distance,
            'geometry' => $route->geometry,
            'route_distance' => $route->distance,
            'route_duration' => $route->duration,
            'timings' => $route->timings,
        ]);

        return $trip;
    }
}
