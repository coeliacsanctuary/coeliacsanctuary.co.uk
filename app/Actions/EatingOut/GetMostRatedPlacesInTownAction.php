<?php

declare(strict_types=1);

namespace App\Actions\EatingOut;

use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Queries\EatingOut\TownReviewsQuery;
use App\Resources\EatingOut\CountyEateryResource;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class GetMostRatedPlacesInTownAction
{
    /** @return Collection<int, CountyEateryResource> */
    public function handle(EateryTown $town): Collection
    {
        /** @var EateryCounty $county */
        $county = $town->county;

        $key = str_replace(
            ['{county.slug}', '{town.slug}'],
            [$county->slug, $town->slug],
            config('coeliac.cacheable.eating-out.most-rated-in-town'),
        );

        return Cache::rememberForever($key, fn () => app(TownReviewsQuery::class)($town, 'rating_count desc, rating desc'));
    }
}
