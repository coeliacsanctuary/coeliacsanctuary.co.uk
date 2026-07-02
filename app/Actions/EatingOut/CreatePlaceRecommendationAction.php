<?php

declare(strict_types=1);

namespace App\Actions\EatingOut;

use App\Jobs\EatingOut\SendEateryRecommendationToAiJob;
use App\Models\EatingOut\EateryRecommendation;
use Illuminate\Support\Arr;

class CreatePlaceRecommendationAction
{
    public function handle(array $data): EateryRecommendation
    {
        $recommendation = EateryRecommendation::query()->create([
            'name' => Arr::get($data, 'name'),
            'email' => Arr::get($data, 'email'),
            'place_name' => Arr::get($data, 'place.name'),
            'place_location' => Arr::get($data, 'place.location'),
            'place_web_address' => Arr::get($data, 'place.url'),
            'place_venue_type_id' => Arr::get($data, 'place.venueType'),
            'place_details' => Arr::get($data, 'place.details'),
        ]);

        SendEateryRecommendationToAiJob::dispatch($recommendation);

        return $recommendation;
    }
}
