<?php

declare(strict_types=1);

namespace App\Actions\EatingOut\TripPlanner;

use App\DataObjects\EatingOut\TripRoute;

class GetTripStopsAction
{
    /**
     * @param  array<int, array{label: string, lat: float, lng: float}>  $points
     * @return array<int, array{number: int, type: 'via' | 'suggested', label: string | null, duration: float, distanceAlongRoute: float, lat: float, lng: float}>
     */
    public function handle(TripRoute $route, array $points, ?int $intervalMinutes): array
    {
        $viaPoints = array_slice(array_values($points), 1, -1);

        $viaStops = [];

        foreach ($route->locatePoints($viaPoints) as $index => $point) {
            $viaStops[] = [
                'type' => 'via',
                'label' => $viaPoints[$index]['label'],
                'duration' => $point['duration'],
                'distanceAlongRoute' => $point['along'],
                'lat' => $point['lat'],
                'lng' => $point['lng'],
            ];
        }

        $stops = [];
        $legStart = 0.0;

        foreach ([...$viaStops, null] as $viaStop) {
            $legEnd = $viaStop['duration'] ?? $route->duration;

            if ($intervalMinutes) {
                for ($seconds = $legStart + ($intervalMinutes * 60); $seconds < ($legEnd - 1800); $seconds += $intervalMinutes * 60) {
                    $point = $route->pointAtDuration($seconds);

                    $stops[] = [
                        'type' => 'suggested',
                        'label' => null,
                        'duration' => $point['duration'],
                        'distanceAlongRoute' => $point['along'],
                        'lat' => $point['lat'],
                        'lng' => $point['lng'],
                    ];
                }
            }

            if ($viaStop) {
                $stops[] = $viaStop;
                $legStart = $viaStop['duration'];
            }
        }

        $numberedStops = [];

        foreach ($stops as $index => $stop) {
            $numberedStops[] = ['number' => $index + 1, ...$stop];
        }

        return $numberedStops;
    }
}
