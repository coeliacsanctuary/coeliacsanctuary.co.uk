<?php

declare(strict_types=1);

namespace App\Actions\EatingOut;

use App\Jobs\EatingOut\SendEateryRecommendationToAiJob;
use App\Models\EatingOut\EateryRecommendation;

class CheckEateryRecommendationWithAiAction
{
    public function handle(EateryRecommendation $eateryRecommendation): void
    {
        $eateryRecommendation->aiData()->updateOrCreate([], [
            'place_name' => null,
            'place_address' => null,
            'place_country' => null,
            'place_county' => null,
            'place_town' => null,
            'place_area' => null,
            'latitude' => null,
            'longitude' => null,
            'phone_number' => null,
            'website' => null,
            'facebook' => null,
            'instagram' => null,
            'eatery_type' => null,
            'venue_type' => null,
            'cuisine' => null,
            'info' => null,
            'features' => null,
            'explanation' => null,
            'is_eligible' => null,
            'completed_at' => null,
            'failed_at' => null,
        ]);

        SendEateryRecommendationToAiJob::dispatch($eateryRecommendation);
    }
}
