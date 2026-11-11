<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\EatingOut\Random;

use App\Models\EatingOut\Eatery;
use App\Resources\EatingOut\EateryBrowseDetailsResource;
use Illuminate\Http\Request;

class ShowController
{
    public function __invoke(Request $request): EateryBrowseDetailsResource
    {
        $eatery = Eatery::query()
            ->inRandomOrder()
            ->with([
                'country', 'county', 'town', 'town.county', 'restaurants', 'venueType', 'type', 'cuisine', 'reviews',
            ])
            ->first();

        return new EateryBrowseDetailsResource($eatery);
    }
}
