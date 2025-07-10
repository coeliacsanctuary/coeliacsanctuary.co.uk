<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\EatingOut\Details;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryReview;
use App\Resources\EatingOut\EateryAppResource;
use App\Resources\EatingOut\EateryBrowseDetailsResource;
use App\Support\Helpers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

class ShowController
{
    public function __invoke(Request $request, Eatery $eatery): EateryBrowseDetailsResource|EateryAppResource
    {
        $eatery->load([
            'country', 'county', 'town', 'town.county', 'restaurants', 'venueType', 'type', 'cuisine',
            'reviews' => function (HasMany $builder) use ($request) {
                /** @var HasMany<EateryReview, Eatery> $builder */
                return $builder->where('approved', 1)
                    ->when($request->has('branchId'), fn (Builder $builder) => $builder->where('nationwide_branch_id', $request->integer('branchId')))
                    ->latest();
            }
        ]);

        if ($request->has('branchId')) {
            $branch = $eatery->nationwideBranches()->where('id', $request->integer('branchId'))->firstOrFail();

            $eatery->setRelation('branch', $branch);
        }

        if (Helpers::requestIsFromApp($request)) {
            return new EateryAppResource($eatery);
        }

        return new EateryBrowseDetailsResource($eatery);
    }
}
