<?php

declare(strict_types=1);

namespace App\Nova\Actions\EatingOut;

use App\Models\EatingOut\EateryRecommendation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

/**
 * @codeCoverageIgnore
 */
class ConvertRecommendationToEatery extends Action
{
    public function handle(ActionFields $fields, Collection $models)
    {
        /** @var EateryRecommendation $recommendation */
        $recommendation = $models->first();

        Cache::put('admin-recommend-place', array_filter([
            'place_recommendation_id' => $recommendation->id,
            'place_name' => $recommendation->place_name,
            'place_location' => $recommendation->place_location,
            'place_web_address' => $recommendation->place_web_address,
            'place_venue_type_id' => $recommendation->place_venue_type_id,
            'place_details' => $recommendation->place_details,
        ]), now()->addMinute());

        return Action::visit('/resources/eateries/new');
    }
}
