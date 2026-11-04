<?php

declare(strict_types=1);

namespace App\DataObjects\EatingOut;

use Illuminate\Support\Collection;

final readonly class TripRoute
{
    /**
     * @param  Collection<int, LatLng>  $points
     * @param  array<int, array{from: int, to: int, distance: float, duration: float}>  $timings
     */
    public function __construct(
        public string $geometry,
        public Collection $points,
        public float $distance,
        public float $duration,
        public array $timings,
    ) {
    }

    /** @return Collection<int, TripRouteChunk> */
    public function chunks(float $padding, float $chunkLength = 25000): Collection
    {
        $measured = $this->measuredPoints();
        $chunks = collect();
        $chunk = [$measured[0]];

        for ($index = 1, $total = count($measured); $index < $total; $index++) {
            $chunk[] = $measured[$index];

            if ($measured[$index]['along'] - $chunk[0]['along'] >= $chunkLength || $index === $total - 1) {
                $chunks->push(TripRouteChunk::fromPoints($chunk, $padding));

                $chunk = [$measured[$index]];
            }
        }

        return $chunks;
    }

    /**
     * @param  Collection<int, TripRouteChunk>  $chunks
     * @return array{distance: float, along: float, duration: float}
     */
    public function locate(Collection $chunks, float $lat, float $lng): array
    {
        /** @var array{distance: float, along: float, duration: float} $closest */
        $closest = $chunks
            ->filter(fn (TripRouteChunk $chunk): bool => $chunk->contains($lat, $lng))
            ->map(fn (TripRouteChunk $chunk): array => $chunk->project($lat, $lng))
            ->sortBy('distance')
            ->first() ?? ['distance' => INF, 'along' => 0.0, 'duration' => 0.0];

        return $closest;
    }

    /** @return array<int, array{lat: float, lng: float, along: float, duration: float}> */
    protected function measuredPoints(): array
    {
        $along = [0.0];

        for ($index = 1, $total = $this->points->count(); $index < $total; $index++) {
            $along[$index] = $along[$index - 1] + $this->metersBetween($this->points[$index - 1], $this->points[$index]);
        }

        $durations = array_fill(0, count($along), 0.0);

        foreach ($this->timings as $timing) {
            $stepStart = $durations[$timing['from']];
            $stepLength = $along[$timing['to']] - $along[$timing['from']];

            for ($index = $timing['from'] + 1; $index <= $timing['to']; $index++) {
                $progress = $stepLength > 0 ? ($along[$index] - $along[$timing['from']]) / $stepLength : 1;

                $durations[$index] = $stepStart + ($timing['duration'] * $progress);
            }
        }

        return $this->points
            ->map(fn (LatLng $point, int $index): array => [
                'lat' => $point->lat,
                'lng' => $point->lng,
                'along' => $along[$index],
                'duration' => $durations[$index],
            ])
            ->all();
    }

    protected function metersBetween(LatLng $from, LatLng $to): float
    {
        $latDelta = deg2rad($to->lat - $from->lat);
        $lngDelta = deg2rad($to->lng - $from->lng);

        $a = sin($latDelta / 2) ** 2 + cos(deg2rad($from->lat)) * cos(deg2rad($to->lat)) * sin($lngDelta / 2) ** 2;

        return 6371000 * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
