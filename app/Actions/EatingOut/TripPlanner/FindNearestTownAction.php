<?php

declare(strict_types=1);

namespace App\Actions\EatingOut\TripPlanner;

use App\Models\EatingOut\EateryTown;

class FindNearestTownAction
{
    public function handle(float $lat, float $lng): ?EateryTown
    {
        return EateryTown::query()
            ->selectRaw('(
                        6371000 * acos (
                          cos ( radians(?) )
                          * cos( radians( CAST(SUBSTRING_INDEX(latlng, \',\', 1) AS DECIMAL(10,7)) ) )
                          * cos( radians( CAST(SUBSTRING_INDEX(latlng, \',\', -1) AS DECIMAL(10,7)) ) - radians(?) )
                          + sin ( radians(?) )
                          * sin( radians( CAST(SUBSTRING_INDEX(latlng, \',\', 1) AS DECIMAL(10,7)) ) )
                        )
                     ) AS distance', [$lat, $lng, $lat])
            ->addSelect(['id', 'town', 'county_id'])
            ->with('county')
            ->whereNotNull('latlng')
            ->whereNot('town', 'nationwide')
            ->orderBy('distance')
            ->first();
    }
}
