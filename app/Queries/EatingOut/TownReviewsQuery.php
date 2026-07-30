<?php

declare(strict_types=1);

namespace App\Queries\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryTown;
use App\Resources\EatingOut\CountyEateryResource;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TownReviewsQuery
{
    /** @return Collection<int, CountyEateryResource> */
    public function __invoke(EateryTown $town, string $rating): Collection
    {
        return $town->eateries()
            ->whereHas('reviews')
            ->where('closed_down', false)
            ->leftJoin('wheretoeat_reviews', fn (JoinClause $join) => $join->on('wheretoeat_reviews.wheretoeat_id', 'wheretoeat.id')->where('approved', true))
            ->select('wheretoeat.*')
            ->addSelect(DB::raw('avg(rating) as rating'))
            ->addSelect(DB::raw('count(wheretoeat_reviews.wheretoeat_id) as rating_count'))
            ->with(['area', 'county', 'restaurants'])
            ->groupBy('wheretoeat.id')
            /** @phpstan-ignore argument.type */
            ->orderByRaw($rating)
            ->take(3)
            ->get()
            ->map(function (Eatery $eatery) use ($town) {
                $eatery->setRelation('town', $town);

                return $eatery;
            })
            ->map(fn (Eatery $eatery) => new CountyEateryResource($eatery));
    }
}
