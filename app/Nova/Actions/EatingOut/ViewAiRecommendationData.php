<?php

declare(strict_types=1);

namespace App\Nova\Actions\EatingOut;

use App\Models\EatingOut\EateryRecommendation;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @codeCoverageIgnore
 */
class ViewAiRecommendationData extends Action
{
    public $name = 'View AI Data';

    public $withoutActionEvents = true;

    public function handle(ActionFields $fields, Collection $models): mixed
    {
        /** @var EateryRecommendation $recommendation */
        $recommendation = $models->first();

        $recommendation->load('aiData');

        $aiData = $recommendation->aiData;

        return Action::modal('view-ai-recommendation-data', [
            'is_eligible' => $aiData->is_eligible,
            'explanation' => $aiData->explanation,
            'place_name' => $aiData->place_name,
            'place_address' => $aiData->place_address,
            'place_country' => $aiData->place_country,
            'place_county' => $aiData->place_county,
            'place_town' => $aiData->place_town,
            'place_area' => $aiData->place_area,
            'latitude' => $aiData->latitude,
            'longitude' => $aiData->longitude,
            'phone_number' => $aiData->phone_number,
            'website' => $aiData->website,
            'facebook' => $aiData->facebook,
            'instagram' => $aiData->instagram,
            'eatery_type' => $aiData->eatery_type,
            'venue_type' => $aiData->venue_type,
            'cuisine' => $aiData->cuisine,
            'info' => $aiData->info,
            'features' => $aiData->features,
        ]);
    }

    public function fields(NovaRequest $request): array
    {
        return [];
    }
}
