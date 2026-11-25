<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\Eateries\Pages;

use App\Filament\Resources\EatingOut\Eateries\EateryResource;
use App\Filament\Resources\EatingOut\Eateries\Schemas\EateryOpeningTimes;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryAttractionRestaurant;
use App\Models\EatingOut\EateryCuisine;
use App\Models\EatingOut\EateryTown;
use Filament\Resources\Pages\CreateRecord;
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

    protected function fillForm(): void
    {
        if ($this->replicate === null) {
            parent::fillForm();

            $this->fillLocationFromTown();
            $this->fillLocationFromArea();
            $this->fillCuisine();

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

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return EateryResource::mutateForSave($data);
    }
}
