<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers\EatingOut\Eatery;

use App\Http\Api\V1\Resources\EatingOut\EateryDetailsResource;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class GetController
{
    public function __invoke(Request $request, Eatery $eatery, NationwideBranch $nationwideBranch): array
    {
        $eatery->load(['county', 'town', 'area', 'features', 'reviews', 'openingTimes']);

        if ($nationwideBranch->exists) {
            abort_if($nationwideBranch->eatery->isNot($eatery), Response::HTTP_NOT_FOUND);

            $nationwideBranch->load(['county', 'town']);

            $eatery->setRelation('branch', $nationwideBranch);
        }

        return [
            'data' => EateryDetailsResource::make($eatery),
        ];
    }
}
