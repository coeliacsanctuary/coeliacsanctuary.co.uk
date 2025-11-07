<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers\EatingOut\Eatery\Branches;

use App\DataObjects\EatingOut\LatLng;
use App\Http\Api\V1\Resources\EatingOut\EateryBranchResource;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Support\Helpers;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class IndexController
{
    public function __invoke(Eatery $eatery, Request $request): array
    {
        if ($eatery->county_id !== 1) {
            abort(404);
        }

        $relations = ['country', 'county', 'town', 'town.county', 'area', 'area.town'];

        $nearbyBranches = [];

        if ($request->filled('latlng')) {
            $latlng = LatLng::fromString($request->string('latlng')->toString());

            $rawNearbyBranches = NationwideBranch::databaseSearchAroundLatLng($latlng, Helpers::milesToMeters(10))
                ->where('wheretoeat_id', $eatery->id)
                ->get();

            $nearbyBranches = $eatery->nationwideBranches()
                ->chaperone()
                ->with($relations)
                ->whereIn('id', $rawNearbyBranches->pluck('id'))
                ->get()
                ->map(fn (NationwideBranch $branch) => $branch->setAttribute('distance', Helpers::metersToMiles((float) ($rawNearbyBranches->find($branch->id)->distance ?? 0))))
                ->sortBy('distance');
        }

        $branches = $eatery->nationwideBranches()
            ->chaperone()
            ->with($relations)
            ->get()
            ->groupBy(fn (NationwideBranch $branch) => $branch->country->country)            /** @phpstan-ignore-line */
            ->sortKeys()
            ->map(
                fn (Collection $branches) => $branches
                    ->groupBy(fn (NationwideBranch $branch) => $branch->county->county)                    /** @phpstan-ignore-line */
                    ->sortKeys()
                    ->map(
                        fn (Collection $branches) => $branches
                            ->groupBy(fn (NationwideBranch $branch) => $branch->town->town)                            /** @phpstan-ignore-line */
                            ->sortKeys()
                            ->map(
                                fn (Collection $branches) => $branches
                                    ->groupBy(fn (NationwideBranch $branch) => $branch->area?->area ?? '_')                                    /** @phpstan-ignore-line */
                                    ->sortKeys()
                                    ->map(
                                        fn (Collection $branches) => $branches
                                            ->sortBy('name')
                                            ->map(fn (NationwideBranch $branch) => EateryBranchResource::make($branch)->toArray($request))
                                            ->values()
                                    )
                            )
                    )
            )
            ->toArray();

        return [
            'data' => [
                'nearby' => EateryBranchResource::collection($nearbyBranches),
                'branches' => $branches,
            ],
        ];
    }
}
