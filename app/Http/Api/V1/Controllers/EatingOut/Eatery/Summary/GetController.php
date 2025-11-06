<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers\EatingOut\Eatery\Summary;

use App\Http\Api\V1\Resources\EatingOut\EaterySummaryResource;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryReview;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;

class GetController
{
    public function __invoke(Request $request, Eatery $eatery): array
    {
        $eatery->load([
            'country', 'county', 'town', 'town.county', 'restaurants', 'venueType', 'type', 'cuisine',
            'reviews' => function (HasMany $builder) use ($request) {
                /** @var HasMany<EateryReview, Eatery> $builder */
                return $builder->where('approved', 1)
                    ->where('admin_review', false)
                    ->when($request->has('branchId'), fn (Builder $builder) => $builder->where('nationwide_branch_id', $request->integer('branchId')))
                    ->latest();
            },
        ]);

        if ($request->has('branchId')) {
            $branch = $eatery->nationwideBranches()->where('id', $request->integer('branchId'))->firstOrFail();

            $eatery->setRelation('branch', $branch);
        }

        return [
            'data' => EaterySummaryResource::make($eatery),
        ];
    }
}
