<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Resources\EatingOut;

use App\Enums\EatingOut\EateryType;
use App\Models\EatingOut\Eatery;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Eatery */
class NationwideEateryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'title' => $this->name,
            'id' => $this->id,
            'info' => $this->type_id === EateryType::ATTRACTION ? $this->restaurants->first()?->info : $this->info,
            'website' => $this->website,
            'type' => EateryType::from((int) $this->type_id)->name(),
            'average_rating' => $this->average_rating,
            'number_of_ratings' => $this->reviews->count(),
            'venueType' => $this->venueType?->venue_type,
            'cuisine' => $this->cuisine?->cuisine,
            'average_expense' => $this->average_expense,
            'number_of_branches' => $this->nationwide_branches_count,
            'nearby_branches' => $this->nearby_branches_count ?: 0,
        ];
    }
}
