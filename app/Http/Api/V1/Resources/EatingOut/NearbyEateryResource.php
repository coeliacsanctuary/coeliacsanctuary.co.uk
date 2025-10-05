<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Resources\EatingOut;

use App\Enums\EatingOut\EateryType;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Support\Helpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Eatery */
class NearbyEateryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var NationwideBranch | null $branch */
        $branch = $this->relationLoaded('branch') ? $this->branch : null;

        return [
            'title' => $branch && $branch->name ? $branch->name : $this->name,
            'key' => $branch ? "{$this->id}-{$branch->id}" : $this->id,
            'id' => $this->id,
            'branch_id' => $branch?->id,
            'address' => $branch->formatted_address ?? $this->formatted_address,
            'type' => EateryType::from((int) $this->type_id)->name(),
            'average_rating' => $branch->average_rating ?? $this->average_rating,
            'number_of_ratings' => $branch?->reviews->count() ?? $this->reviews->count(),
            'distance' => Helpers::metersToMiles($branch->distance ?? $this->distance ?? 0),
            'venueType' => $this->venueType?->venue_type,
            'average_expense' => $this->average_expense,
        ];
    }
}
