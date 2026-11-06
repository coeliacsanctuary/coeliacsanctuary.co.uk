<?php

declare(strict_types=1);

namespace App\Actions\EatingOut\TripPlanner;

use App\DataObjects\EatingOut\LatLng;
use Illuminate\Support\Collection;

class DecodePolylineAction
{
    /** @return Collection<int, LatLng> */
    public function handle(string $encoded): Collection
    {
        $points = [];
        $index = 0;
        $length = mb_strlen($encoded);
        $lat = 0;
        $lng = 0;

        while ($index < $length) {
            $lat += $this->decodeValue($encoded, $index);
            $lng += $this->decodeValue($encoded, $index);

            $points[] = new LatLng($lat / 1e5, $lng / 1e5);
        }

        return collect($points);
    }

    protected function decodeValue(string $encoded, int &$index): int
    {
        $result = 0;
        $shift = 0;

        do {
            $byte = ord($encoded[$index++]) - 63;
            $result |= ($byte & 0x1F) << $shift;
            $shift += 5;
        } while ($byte >= 0x20);

        return ($result & 1) ? ~($result >> 1) : ($result >> 1);
    }
}
