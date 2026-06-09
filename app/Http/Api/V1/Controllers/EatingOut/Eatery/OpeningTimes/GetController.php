<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers\EatingOut\Eatery\OpeningTimes;

use App\Http\Api\V1\Resources\EatingOut\EateryOpeningTimesResource;
use App\Models\EatingOut\Eatery;

class GetController
{
    public function __invoke(Eatery $eatery): array
    {
        abort_if( ! $eatery->openingTimes, 404);

        return [
            'data' => EateryOpeningTimesResource::make($eatery->openingTimes),
        ];
    }
}
