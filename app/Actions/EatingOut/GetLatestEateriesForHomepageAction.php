<?php

declare(strict_types=1);

namespace App\Actions\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Resources\EatingOut\SimpleEateryResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class GetLatestEateriesForHomepageAction
{
    public function handle(): AnonymousResourceCollection
    {
        /** @var string $key */
        $key = config('coeliac.cacheable.eating-out.home');

        /** @var AnonymousResourceCollection $eateries */
        $eateries = Cache::rememberForever(
            $key,
            function () {
                $eateries = Eatery::query()
                    ->with(['town', 'area', 'county', 'town.county', 'country'])
                    ->take(8)
                    ->latest()
                    ->get();

                $branches = NationwideBranch::query()
                    ->with(['eatery', 'area', 'town', 'county', 'town.county', 'country'])
                    ->take(8)
                    ->latest()
                    ->get();

                $combined = collect([...$eateries, ...$branches])
                    ->sortByDesc('created_at')
                    ->take(8);

                return SimpleEateryResource::collection($combined);
            }
        );

        return $eateries;
    }
}
