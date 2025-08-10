<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\EatingOut\Ratings\Latest;

use App\Models\EatingOut\EateryReview;
use App\Resources\EatingOut\Api\EateryApiReviewResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class IndexController
{
    public function __invoke(): AnonymousResourceCollection
    {
        $reviews = EateryReview::query()
            ->latest()
            ->with(['eatery', 'eatery.area', 'eatery.town', 'eatery.county', 'eatery.country'])
            ->take(5)
            ->get();

        return EateryApiReviewResource::collection($reviews);
    }
}
