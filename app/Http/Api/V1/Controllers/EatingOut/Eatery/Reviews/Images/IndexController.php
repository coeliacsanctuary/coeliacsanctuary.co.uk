<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers\EatingOut\Eatery\Reviews\Images;

use App\Http\Api\V1\Resources\EatingOut\EateryReviewImageResource;
use App\Models\EatingOut\Eatery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class IndexController
{
    public function __invoke(Request $request, Eatery $eatery): array
    {
        $images = $eatery->reviewImages()
            ->whereHas(
                'review',
                fn ($query) => $query
                    ->where('admin_review', false) /** @phpstan-ignore-line */
                    ->when($request->filled('branchId'), fn (Builder $builder) => $builder->where('nationwide_branch_id', $request->integer('branchId'))) /** @phpstan-ignore-line */
            )
            ->get();

        return [
            'data' => EateryReviewImageResource::collection($images),
        ];
    }
}
