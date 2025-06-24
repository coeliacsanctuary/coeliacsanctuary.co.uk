<?php

declare(strict_types=1);

namespace App\Resources\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryAttractionRestaurant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Eatery */
class EateryBrowseDetailsResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'name' => $this->relationLoaded('branch') && $this->branch && $this->branch->name ? "{$this->branch->name} - ({$this->name})" : $this->name,
            'link' => $this->relationLoaded('branch') ? $this->branch?->link() : $this->link(),
            'full_location' => $this->full_location,
            'venue_type' => $this->venueType?->venue_type,
            'type' => $this->type?->name,
            'cuisine' => $this->cuisine?->cuisine,
            'website' => $this->website,
            'restaurants' => $this->restaurants->map(fn (EateryAttractionRestaurant $restaurant): array => [
                'name' => $restaurant->restaurant_name,
                'info' => $restaurant->info,
            ]),
            'info' => $this->info,
            'location' => [
                'address' => collect(explode("\n", $this->relationLoaded('branch') && $this->branch ? $this->branch->address : $this->address))
                    ->map(fn (string $line) => mb_trim($line))
                    ->join(', '),
                'lat' => $this->lat,
                'lng' => $this->lng,
            ],
            'phone' => $this->phone,
            'reviews' => [
                'number' => $this->reviews->count(),
                'average' => (float) $this->average_rating,
            ],
        ];
    }
}
