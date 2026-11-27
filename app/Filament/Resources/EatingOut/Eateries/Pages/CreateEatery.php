<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\Eateries\Pages;

use App\Enums\EatingOut\EateryType;
use App\Filament\Resources\EatingOut\Eateries\EateryResource;
use App\Filament\Resources\EatingOut\Eateries\Schemas\EateryOpeningTimes;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryAttractionRestaurant;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryCuisine;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryRecommendation;
use App\Models\EatingOut\EateryRecommendationAiData;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\EateryVenueType;
use App\Notifications\EatingOut\EateryRecommendationAddedNotification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\AnonymousNotifiable;
use Livewire\Attributes\Url;

class CreateEatery extends CreateRecord
{
    protected static string $resource = EateryResource::class;

    #[Url]
    public ?int $replicate = null;

    #[Url]
    public ?int $town = null;

    #[Url]
    public ?int $area = null;

    #[Url]
    public ?int $cuisine = null;

    #[Url]
    public ?int $recommendation = null;

    protected function fillForm(): void
    {
        if ($this->replicate === null) {
            parent::fillForm();

            $this->fillLocationFromTown();
            $this->fillLocationFromArea();
            $this->fillCuisine();
            $this->fillFromRecommendation();

            return;
        }

        $eatery = Eatery::query()
            ->withoutGlobalScopes()
            ->with(['features', 'openingTimes', 'restaurants'])
            ->findOrFail($this->replicate);

        $this->callHook('beforeFill');

        $this->form->fill([
            ...$eatery->only([
                'name', 'live',
                'country_id', 'county_id', 'town_id', 'area_id', 'address', 'lat', 'lng',
                'phone', 'website', 'gf_menu_link', 'facebook_url', 'instagram_url',
                'type_id', 'venue_type_id', 'cuisine_id', 'info', 'snippet',
            ]),
            'is_nationwide' => $eatery->county_id === 1,
            'map' => ['lat' => $eatery->lat, 'lng' => $eatery->lng],
            'features' => $eatery->features->pluck('id')->all(),
            'restaurants' => $eatery->restaurants
                ->map(fn (EateryAttractionRestaurant $restaurant): array => $restaurant->only(['restaurant_name', 'info']))
                ->all(),
            'no_opening_times' => $eatery->openingTimes === null,
            'openingTimes' => EateryOpeningTimes::toFormState($eatery->openingTimes),
        ]);

        $this->callHook('afterFill');
    }

    protected function fillLocationFromTown(): void
    {
        if ($this->town === null) {
            return;
        }

        $town = EateryTown::query()
            ->withoutGlobalScopes()
            ->with(['county' => fn ($query) => $query->withoutGlobalScopes()])
            ->findOrFail($this->town);

        $this->data['country_id'] = $town->county?->country_id;
        $this->data['county_id'] = $town->county_id;
        $this->data['town_id'] = $town->id;
    }

    protected function fillLocationFromArea(): void
    {
        if ($this->area === null) {
            return;
        }

        $area = EateryArea::query()
            ->withoutGlobalScopes()
            ->with([
                'town' => fn ($query) => $query->withoutGlobalScopes(),
                'town.county' => fn ($query) => $query->withoutGlobalScopes(),
            ])
            ->findOrFail($this->area);

        $this->data['country_id'] = $area->town?->county?->country_id;
        $this->data['county_id'] = $area->town?->county_id;
        $this->data['town_id'] = $area->town_id;
        $this->data['area_id'] = $area->id;
    }

    protected function fillCuisine(): void
    {
        if ($this->cuisine === null) {
            return;
        }

        $this->data['cuisine_id'] = EateryCuisine::query()->findOrFail($this->cuisine)->id;
    }

    public function recommendationAiData(): ?EateryRecommendationAiData
    {
        return once(fn (): ?EateryRecommendationAiData => $this->eateryRecommendation()?->aiData);
    }

    protected function eateryRecommendation(): ?EateryRecommendation
    {
        if ($this->recommendation === null) {
            return null;
        }

        return once(fn (): EateryRecommendation => EateryRecommendation::query()->with('aiData')->findOrFail($this->recommendation));
    }

    protected function fillFromRecommendation(): void
    {
        $recommendation = $this->eateryRecommendation();

        if ( ! $recommendation) {
            return;
        }

        $aiData = $recommendation->aiData;

        $latitude = $aiData?->latitude;
        $longitude = $aiData?->longitude;

        $this->data = [
            ...$this->data,
            ...array_filter([
                'name' => $aiData->place_name ?? $recommendation->place_name,
                'address' => $aiData->place_address ?? $recommendation->place_location,
                'country_id' => $this->resolveRelatedId($aiData?->place_country, null, EateryCountry::class, 'country'),
                'county_id' => $this->resolveRelatedId($aiData?->place_county, null, EateryCounty::class, 'county'),
                'town_id' => $this->resolveRelatedId($aiData?->place_town, null, EateryTown::class, 'town'),
                'area_id' => $this->resolveRelatedId($aiData?->place_area, null, EateryArea::class, 'area'),
                'lat' => $latitude,
                'lng' => $longitude,
                'map' => $latitude !== null && $longitude !== null ? ['lat' => (float) $latitude, 'lng' => (float) $longitude] : null,
                'phone' => $aiData?->phone_number,
                'website' => $aiData->website ?? $recommendation->place_web_address,
                'facebook_url' => $aiData?->facebook,
                'instagram_url' => $aiData?->instagram,
                'type_id' => collect(EateryType::cases())->first(fn (EateryType $type): bool => $type->name() === $aiData?->eatery_type)?->value,
                'venue_type_id' => $this->resolveRelatedId($aiData?->venue_type, $recommendation->place_venue_type_id, EateryVenueType::class, 'venue_type'),
                'cuisine_id' => $this->resolveRelatedId($aiData?->cuisine, null, EateryCuisine::class, 'cuisine'),
                'info' => $aiData?->info ? "{$aiData->info}\n\nSubmitted Data:\n\n{$recommendation->place_details}" : $recommendation->place_details,
                'features' => $aiData?->features ? EateryFeature::query()->whereIn('feature', $aiData->features)->pluck('id')->all() : null,
            ], fn (mixed $value): bool => $value !== null),
        ];
    }

    /** @param class-string<Model> $model */
    protected function resolveRelatedId(?string $rawValue, ?int $default, string $model, string $column): ?int
    {
        if ( ! $rawValue) {
            return $default;
        }

        return $model::query()
            ->withoutGlobalScopes()
            ->where($column, $rawValue)
            ->first()
            ?->getKey();
    }

    protected function afterCreate(): void
    {
        $recommendation = $this->eateryRecommendation();

        if ( ! $recommendation) {
            return;
        }

        /** @var Eatery $eatery */
        $eatery = $this->getRecord();

        $recommendation->update(['completed' => true]);

        new AnonymousNotifiable()
            ->route('mail', $recommendation->email)
            ->notify(new EateryRecommendationAddedNotification($recommendation, $eatery));
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return EateryResource::mutateForSave($data);
    }
}
