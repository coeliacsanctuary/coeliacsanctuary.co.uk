<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers\EatingOut\Eatery\SuggestEdits;

use App\Http\Api\V1\Resources\EatingOut\EaterySuggestEditResource;
use App\Models\EatingOut\Eatery;

class GetController
{
    public function __invoke(Eatery $eatery): array
    {
        $eatery->load(['venueType', 'cuisine', 'openingTimes', 'features']);

        return [
            'data' => EaterySuggestEditResource::make($eatery),
        ];
    }
}
