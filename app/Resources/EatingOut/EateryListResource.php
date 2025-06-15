<?php

declare(strict_types=1);

namespace App\Resources\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryAttractionRestaurant;
use App\Models\EatingOut\EateryType;
use App\Models\EatingOut\EateryVenueType;
use App\Models\EatingOut\NationwideBranch;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Eatery */
class EateryListResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request)
    {
        /** @var NationwideBranch | null $branch */
        $branch = $this->relationLoaded('branch') ? $this->branch : null;

        /** @var EateryVenueType $venueType */
        $venueType = $this->venueType;

        /** @var EateryType $eateryType */
        $eateryType = $this->type;

        if ($branch) {
            $this->reviews = $this->reviews->where('nationwide_branch_id', $branch->id);
        }

        return [
            'name' => $this->name,
            'key' => $this->id . ($branch ? '-' . $branch->id : null),
            'isNationwideBranch' => $this->relationLoaded('branch'),
            'link' => $this->link(),
            'county' => [
                'id' => $this->county_id,
                'name' => $this->county?->county,
                'link' => $this->county?->link(),
            ],
            'town' => [
                'id' => $this->town_id,
                'name' => $this->town?->town,
                'link' => $this->town?->link(),
            ],
            'branch' => $branch ? [
                'id' => $branch->id,
                'name' => $branch->name,
                'location' => [
                    'lat' => $branch->lat,
                    'lng' => $branch->lng,
                    'address' => collect(explode("\n", $branch->address))
                        ->map(fn (string $line) => mb_trim($line))
                        ->join(', '),
                ],
                'link' => $branch->link(),
            ] : null,
            'venue_type' => $venueType->venue_type,
            'type' => $eateryType->name,
            'cuisine' => $this->cuisine?->cuisine,
            'website' => $this->website,
            'restaurants' => $this->restaurants->map(fn (EateryAttractionRestaurant $restaurant): array => [
                'name' => $restaurant->restaurant_name,
                'info' => $restaurant->info,
            ]),
            'info' => $this->info,
            'is_fully_gf' => $this->features->where('feature', '100% Gluten Free')->isNotEmpty(),
            'location' => [
                'address' => collect(explode("\n", $this->address))
                    ->map(fn (string $line) => mb_trim($line))
                    ->join(', '),
                'lat' => $this->lat,
                'lng' => $this->lng,
            ],
            'phone' => $this->phone,
            'reviews' => [
                'number' => $this->reviews->count(),
                'average' => $this->average_rating,
            ],
            'distance' => $branch->distance ?? $this->distance,
        ];
    }
}
