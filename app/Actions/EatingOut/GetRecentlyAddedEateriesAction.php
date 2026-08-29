<?php

declare(strict_types=1);

namespace App\Actions\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Resources\EatingOut\SimpleEateryResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class GetRecentlyAddedEateriesAction
{
    protected int $take = 5;

    public function handle(): AnonymousResourceCollection
    {
        /** @var string $key */
        $key = config('coeliac.cacheable.eating-out.recently-added');

        /** @var AnonymousResourceCollection $eateries */
        $eateries = Cache::rememberForever(
            $key,
            function () {
                $eateries = Eatery::query()
                    ->with(['town', 'area', 'county', 'town.county', 'country'])
                    ->take($this->take)
                    ->latest()
                    ->get();

                $branches = NationwideBranch::query()
                    ->with(['eatery', 'area', 'town', 'county', 'town.county', 'country'])
                    ->take($this->take)
                    ->latest()
                    ->get();

                $combined = collect([...$eateries, ...$branches])
                    ->sortByDesc('created_at')
                    ->take($this->take);

                return SimpleEateryResource::collection($combined);
            }
        );

        return $eateries;
    }
}
