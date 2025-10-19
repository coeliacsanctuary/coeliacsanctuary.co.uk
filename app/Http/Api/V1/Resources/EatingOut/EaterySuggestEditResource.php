<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Resources\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCuisine;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryVenueType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Eatery */
class EaterySuggestEditResource extends JsonResource
{
    public static $wrap = 'data';

    public function toArray(Request $request): array
    {
        return [
            'address' => $this->address,
            'website' => $this->website,
            'gf_menu_link' => $this->gf_menu_link,
            'phone' => $this->phone,
            'type_id' => $this->type_id,
            'venue_type' => $this->getVenueType(),
            'cuisine' => $this->getCuisine(),
            'opening_times' => [
                'today' => $this->openingTimes?->is_open_now ? [$this->openingTimes->opensAt(), $this->openingTimes->closesAt()] : null,
                'monday' => [$this->openingTimes?->monday_start, $this->openingTimes?->monday_end],
                'tuesday' => [$this->openingTimes?->tuesday_start, $this->openingTimes?->tuesday_end],
                'wednesday' => [$this->openingTimes?->wednesday_start, $this->openingTimes?->wednesday_end],
                'thursday' => [$this->openingTimes?->thursday_start, $this->openingTimes?->thursday_end],
                'friday' => [$this->openingTimes?->friday_start, $this->openingTimes?->friday_end],
                'saturday' => [$this->openingTimes?->saturday_start, $this->openingTimes?->saturday_end],
                'sunday' => [$this->openingTimes?->sunday_start, $this->openingTimes?->sunday_end],
            ],
            'features' => $this->getFeatures(),
            'is_nationwide' => $this->county_id === 1,
        ];
    }

    protected function getVenueType(): array
    {
        /** @var EateryVenueType $eateryVenueType */
        $eateryVenueType = $this->venueType;

        return [
            'id' => $eateryVenueType->id,
            'label' => $eateryVenueType->venue_type,
            'values' => EateryVenueType::query()
                ->orderBy('venue_type')
                ->get()
                ->map(fn (EateryVenueType $venueType) => [
                    'value' => $venueType->id,
                    'label' => $venueType->venue_type,
                    'selected' => $venueType->id === $eateryVenueType->id,
                ]),
        ];
    }

    protected function getCuisine(): array
    {
        return [
            'id' => $this->cuisine?->id ?: null,
            'label' => $this->cuisine?->cuisine ?: null,
            'values' => EateryCuisine::query()
                ->orderBy('cuisine')
                ->get()
                ->map(fn (EateryCuisine $cuisine) => [
                    'value' => $cuisine->id,
                    'label' => $cuisine->cuisine,
                    'selected' => $cuisine->id === $this->cuisine?->id,
                ]),
        ];
    }

    protected function getFeatures(): array
    {
        return [
            'selected' => $this->features->map(fn (EateryFeature $feature) => [
                'id' => $feature->id,
                'label' => $feature->feature,
            ]),
            'values' => EateryFeature::query()
                ->orderBy('feature')
                ->get()
                ->map(fn (EateryFeature $feature) => [
                    'value' => $feature->id,
                    'label' => $feature->feature,
                    'selected' => in_array($feature->id, $this->features->pluck('id')->toArray(), true),
                ]),
        ];
    }
}
