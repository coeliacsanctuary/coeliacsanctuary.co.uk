<?php

declare(strict_types=1);

namespace App\Queries\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Resources\EatingOut\CountyEateryResource;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class MostReviewsInUkQuery
{
    /** @return Collection<int, CountyEateryResource> */
    public function __invoke(string $rating): Collection
    {
        return Eatery::query()
            ->whereHas('reviews')
            ->leftJoin('wheretoeat_reviews', fn (JoinClause $join) => $join->on('wheretoeat_reviews.wheretoeat_id', 'wheretoeat.id')->where('approved', true))
            ->select('wheretoeat.*')
            ->addSelect(DB::raw('avg(rating) as rating'))
            ->addSelect(DB::raw('count(wheretoeat_reviews.wheretoeat_id) as rating_count'))
            ->with(['county', 'town', 'area'])
            ->groupBy('wheretoeat.id')
            ->orderByRaw($rating)
            ->take(3)
            ->get()
            ->map(function (Eatery $eatery) {
                $eatery->town?->setRelation('county', $eatery->county);

                return $eatery;
            })
            ->map(fn (Eatery $eatery) => new CountyEateryResource($eatery));
    }
}
