<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers\EatingOut\Nationwide;

use App\DataObjects\EatingOut\LatLng;
use App\Http\Api\V1\Resources\EatingOut\NationwideEateryResource;
use App\Models\EatingOut\Eatery;
use App\Support\Helpers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class IndexController
{
    public function __invoke(Request $request): array
    {
        $latLng = null;

        if ($request->filled('latlng')) {
            $latLng = LatLng::fromString($request->string('latlng')->toString());
        }

        $eateries = Eatery::query()
            ->whereRelation('county', 'slug', 'nationwide')
            ->with(['reviews', 'restaurants', 'type', 'venueType', 'cuisine'])
            ->withCount([
                'nationwideBranches',
                'nationwideBranches as nearby_branches_count' => fn (Builder $query) => $query->when(
                    $latLng,
                    fn (Builder $query) => $query->whereRaw('
                    (
                        6371000 * acos (
                          cos ( radians(?) )
                          * cos( radians( lat ) )
                          * cos( radians( lng ) - radians(?) )
                          + sin ( radians(?) )
                          * sin( radians( lat ) )
                        )
                     ) < ?', [
                        $latLng->lat,
                        $latLng->lng,
                        $latLng->lat,
                        Helpers::milesToMeters(10),
                    ]),
                    fn (Builder $query) => $query->whereRaw('0 = 1'),
                ),
            ])
            ->orderBy('name')
            ->get();

        return ['data' => NationwideEateryResource::collection($eateries)];
    }
}
