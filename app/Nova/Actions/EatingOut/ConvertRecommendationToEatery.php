<?php

declare(strict_types=1);

namespace App\Nova\Actions\EatingOut;

use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryCuisine;
use App\Models\EatingOut\EateryRecommendation;
use App\Models\EatingOut\EateryRecommendationAiData;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\EateryType;
use App\Models\EatingOut\EateryVenueType;
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

        /** @var EateryRecommendationAiData | null $aiData */
        $aiData = $recommendation->aiData;

        Cache::put('admin-recommend-place', array_filter([
            'place_recommendation_id' => $recommendation->id,
            'place_name' => $aiData->place_name ?? $recommendation->place_name,
            'place_location' => $aiData?->place_address ?? $recommendation->place_location,
            'place_country' => $this->resolveRelatedId($aiData?->place_country, null, EateryCountry::class, 'country'),
            'place_county' => $this->resolveRelatedId($aiData?->place_county, null, EateryCounty::class, 'county'),
            'place_town' => $this->resolveRelatedId($aiData?->place_town, null, EateryTown::class, 'town'),
            'place_area' => $this->resolveRelatedId($aiData?->place_area, null, EateryArea::class, 'area'),
            'latitude' => $aiData?->latitude,
            'longitude' => $aiData?->longitude,
            'phone_number' => $aiData?->phone_number,
            'place_web_address' => $aiData->website ?? $recommendation->place_web_address,
            'place_facebook' => $aiData?->facebook,
            'place_instagram' => $aiData?->instagram,
            'place_type_id' => $this->resolveRelatedId($aiData?->eatery_type, null, EateryType::class, 'type'),
            'place_venue_type_id' => $this->resolveRelatedId($aiData?->venue_type, $recommendation->place_venue_type_id, EateryVenueType::class, 'venue_type'),
            'place_cuisine_id' => $this->resolveRelatedId($aiData?->cuisine, null, EateryCuisine::class, 'cuisine'),
            'place_details' => $aiData?->info ? "{$aiData->info}\n\nSubmitted Data:\n\n{$recommendation->place_details}" : $recommendation->place_details,
            'features' => $aiData?->features,
            'isEligible' => $aiData?->is_eligible,
            'explanation' => $aiData?->explanation,
        ], fn ($value) => $value !== null), now()->addHour());

        return Action::openInNewTab('/cs-adm/resources/eateries/new');
    }

    protected function resolveRelatedId(?string $rawValue, ?int $default, string $model, string $column): ?int
    {
        if ( ! $rawValue) {
            return $default;
        }

        return $model::query()
            ->where($column, $rawValue)
            ->first()
            ?->id;
    }
}
