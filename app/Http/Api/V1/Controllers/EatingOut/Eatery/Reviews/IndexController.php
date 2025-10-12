<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers\EatingOut\Eatery\Reviews;

use App\Http\Api\V1\Controllers\EatingOut\Reviews\Builder;
use App\Http\Api\V1\Resources\EatingOut\EateryReviewResource;
use App\Models\EatingOut\Eatery;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class IndexController
{
    public function __invoke(Request $request, Eatery $eatery): array
    {
        $eatery->load(['reviews' => fn (Relation $builder) => $builder
            ->with(['images'])
            ->when($request->filled('branchId'), fn (Builder $builder) => $builder->where('nationwide_branch_id', $request->integer('branchId')))
            ->where('admin_review', false)
            ->latest(),
        ]);

        return [
            'data' => [
                'average' => $eatery->average_rating,
                'total' => $eatery->reviews->count(),
                'reviews' => EateryReviewResource::collection($eatery->reviews),
            ],
        ];
    }
}
