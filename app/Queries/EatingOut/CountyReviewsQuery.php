<?php

declare(strict_types=1);

namespace App\Queries\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Resources\EatingOut\CountyEateryResource;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class CountyReviewsQuery
{
    /** @return Collection<int, CountyEateryResource> */
    public function __invoke(EateryCounty $county, string $rating): Collection
    {
        return $county->eateries()
            ->whereHas('reviews')
            ->leftJoin('wheretoeat_reviews', fn (JoinClause $join) => $join->on('wheretoeat_reviews.wheretoeat_id', 'wheretoeat.id')->where('approved', true))
            ->select('wheretoeat.*')
            ->addSelect(DB::raw('avg(rating) as rating'))
            ->addSelect(DB::raw('count(wheretoeat_reviews.wheretoeat_id) as rating_count'))
            ->with(['town', 'area'])
            ->groupBy('wheretoeat.id')
            ->orderByRaw($rating)
            ->take(3)
            ->get()
            ->map(function (Eatery $eatery) use ($county) {
                $eatery->setRelation('county', $county);
                $eatery->town?->setRelation('county', $county);

                return $eatery;
            })
            ->map(fn (Eatery $eatery) => new CountyEateryResource($eatery));
    }
}
