<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\EatingOut\Latest;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Resources\EatingOut\Api\EateryApiResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IndexController
{
    public function __invoke(): AnonymousResourceCollection
    {
        $eateries = Eatery::query()
            ->latest()
            ->take(5)
            ->with(['area', 'town', 'county', 'country'])
            ->get();

        $branches = NationwideBranch::query()
            ->with(['eatery', 'area', 'town', 'county', 'town.county', 'country'])
            ->take(5)
            ->latest()
            ->get();

        $combined = collect([...$eateries, ...$branches])
            ->sortByDesc('created_at')
            ->take(5);

        return EateryApiResource::collection($combined);
    }
}
