<?php

declare(strict_types=1);

namespace App\Resources\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryType;
use App\Models\EatingOut\EateryVenueType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Eatery */
class NationwideListResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request)
    {
        /** @var EateryVenueType $venueType */
        $venueType = $this->venueType;

        /** @var EateryType $eateryType */
        $eateryType = $this->type;

        return [
            'name' => $this->name,
            'key' => $this->id,
            'link' => "/wheretoeat/nationwide/{$this->slug}",
            'venue_type' => $venueType->venue_type,
            'type' => $eateryType->name,
            'cuisine' => $this->cuisine?->cuisine,
            'website' => $this->website,
            'info' => $this->info,
            'is_fully_gf' => $this->features->where('feature', '100% Gluten Free')->isNotEmpty(),
            'phone' => $this->phone,
            'reviews' => [
                'number' => $this->reviews->count(),
                'average' => $this->average_rating,
            ],
        ];
    }
}
