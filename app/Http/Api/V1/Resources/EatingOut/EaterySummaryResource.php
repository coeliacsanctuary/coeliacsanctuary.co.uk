<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Resources\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryAttractionRestaurant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Eatery */
class EaterySummaryResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request)
    {
        return [
            'id' => $this->id,
            'branchId' => $this->branch?->id,
            'title' => $this->relationLoaded('branch') && $this->branch && $this->branch->name ? "{$this->branch->name} - {$this->name}" : $this->name,
            'fullLocation' => $this->full_location,
            'venueType' => $this->venueType?->venue_type,
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
            ],
            'phone' => $this->phone,
            'reviews' => [
                'number' => $this->reviews->count(),
                'average' => (float) $this->average_rating,
            ],
        ];
    }
}
