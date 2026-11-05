<?php

declare(strict_types=1);

namespace App\Actions\EatingOut\TripPlanner;

use App\DataObjects\EatingOut\TripRoute;

class GetTripStopsAction
{
    /** @return array<int, array{number: int, duration: float, distanceAlongRoute: float, lat: float, lng: float}> */
    public function handle(TripRoute $route, ?int $intervalMinutes): array
    {
        if ( ! $intervalMinutes) {
            return [];
        }

        $stops = [];
        $interval = $intervalMinutes * 60;

        for ($number = 1; ($number * $interval) < ($route->duration - 1800); $number++) {
            $point = $route->pointAtDuration($number * $interval);

            $stops[] = [
                'number' => $number,
                'duration' => $point['duration'],
                'distanceAlongRoute' => $point['along'],
                'lat' => $point['lat'],
                'lng' => $point['lng'],
            ];
        }

        return $stops;
    }
}
