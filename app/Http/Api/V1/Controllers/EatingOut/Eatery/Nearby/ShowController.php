<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers\EatingOut\Eatery\Nearby;

use App\Actions\EatingOut\GetNearbyEateriesAction;
use App\Models\EatingOut\Eatery;

class ShowController
{
    public function __invoke(Eatery $eatery, GetNearbyEateriesAction $getNearbyEateriesAction): array
    {
        return [
            'data' => [
                'eateries' => $getNearbyEateriesAction->handle($eatery),
            ],
        ];
    }
}
