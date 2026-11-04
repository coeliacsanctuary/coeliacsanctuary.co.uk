<?php

declare(strict_types=1);

namespace App\DataObjects\EatingOut;

final readonly class TripRouteChunk
{
    /**
     * @param  array<int, array{lat: float, lng: float, along: float, duration: float}>  $points
     * @param  array{min: float, max: float}  $latBounds
     * @param  array{min: float, max: float}  $lngBounds
     */
    public function __construct(
        public array $points,
        public array $latBounds,
        public array $lngBounds,
    ) {
    }

    /** @param  array<int, array{lat: float, lng: float, along: float, duration: float}>  $points */
    public static function fromPoints(array $points, float $padding): self
    {
        $lats = array_column($points, 'lat');
        $lngs = array_column($points, 'lng');

        $latPadding = $padding / 111320;
        $lngPadding = $padding / (111320 * cos(deg2rad(max(array_map(abs(...), $lats)))));

        return new self(
            points: $points,
            latBounds: ['min' => min($lats) - $latPadding, 'max' => max($lats) + $latPadding],
            lngBounds: ['min' => min($lngs) - $lngPadding, 'max' => max($lngs) + $lngPadding],
        );
    }

    public function contains(float $lat, float $lng): bool
    {
        return $lat >= $this->latBounds['min'] && $lat <= $this->latBounds['max'] && $lng >= $this->lngBounds['min'] && $lng <= $this->lngBounds['max'];
    }

    /** @return array{distance: float, along: float, duration: float} */
    public function project(float $lat, float $lng): array
    {
        $closest = ['distance' => INF, 'along' => 0.0, 'duration' => 0.0];
        $metersPerLng = 111320 * cos(deg2rad($lat));

        for ($index = 1, $total = count($this->points); $index < $total; $index++) {
            $start = $this->points[$index - 1];
            $end = $this->points[$index];

            $startX = ($start['lng'] - $lng) * $metersPerLng;
            $startY = ($start['lat'] - $lat) * 111320;
            $segmentX = (($end['lng'] - $lng) * $metersPerLng) - $startX;
            $segmentY = (($end['lat'] - $lat) * 111320) - $startY;
            $segmentLength = ($segmentX ** 2) + ($segmentY ** 2);

            $progress = $segmentLength > 0 ? max(0, min(1, -(($startX * $segmentX) + ($startY * $segmentY)) / $segmentLength)) : 0;

            $distance = sqrt((($startX + ($progress * $segmentX)) ** 2) + (($startY + ($progress * $segmentY)) ** 2));

            if ($distance < $closest['distance']) {
                $closest = [
                    'distance' => $distance,
                    'along' => $start['along'] + (($end['along'] - $start['along']) * $progress),
                    'duration' => $start['duration'] + (($end['duration'] - $start['duration']) * $progress),
                ];
            }
        }

        return $closest;
    }
}
