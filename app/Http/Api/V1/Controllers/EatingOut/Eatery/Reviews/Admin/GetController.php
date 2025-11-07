<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers\EatingOut\Eatery\Reviews\Admin;

use App\Http\Api\V1\Resources\EatingOut\EateryReviewResource;
use App\Models\EatingOut\Eatery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class GetController
{
    public function __invoke(Request $request, Eatery $eatery): array
    {
        $adminReview = $eatery
            ->reviews()
            ->where('admin_review', true)
            ->with(['images'])
            ->when($request->filled('branchId'), fn (Builder $builder) => $builder->where('nationwide_branch_id', $request->integer('branchId')))
            ->latest()
            ->first();

        if ( ! $adminReview) {
            abort(404);
        }

        return [
            'data' => EateryReviewResource::make($adminReview),
        ];
    }
}
