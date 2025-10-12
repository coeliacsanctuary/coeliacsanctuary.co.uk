<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Resources\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryAttractionRestaurant;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryOpeningTimes;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\NationwideBranch;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/** @mixin Eatery */
class EateryDetailsResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request)
    {
        /** @var Collection<int, EateryFeature> $features */
        $features = $this->features;

        /** @var NationwideBranch | null $branch */
        $branch = $this->relationLoaded('branch') ? $this->branch : null;

        /** @var EateryOpeningTimes | null $eateryOpeningTimes */
        $eateryOpeningTimes = $this->openingTimes;

        return [
            'id' => $this->id,
            'title' => $this->name,
            'county' => $this->county?->county,
            'town' => $this->town?->town,
            'area' => $this->area?->area,
            'venue_type' => $this->venueType?->venue_type,
            'type' => $this->type?->name,
            'cuisine' => $this->cuisine?->cuisine,
            'website' => $this->website,
            'menu' => $this->gf_menu_link,
            'restaurants' => $this->restaurants->map(fn (EateryAttractionRestaurant $restaurant): array => [
                'name' => $restaurant->restaurant_name,
                'info' => $restaurant->info,
            ]),
            'is_fully_gf' => $this->features->where('feature', '100% Gluten Free')->isNotEmpty(),
            'info' => $this->info,
            'location' => [
                'address' => collect(explode("\n", $this->address))
                    ->map(fn (string $line) => mb_trim($line))
                    ->join(', '),
                'lat' => $this->lat,
                'lng' => $this->lng,
            ],
            'phone' => $this->phone,
            'reviews' => [
                'number' => $branch ? $branch->reviews->count() : $this->reviews->count(),
                'average' => $branch ? $branch->average_rating : $this->average_rating,
                'expense' => $branch ? $branch->average_expense : $this->average_expense,
            ],
            'features' => $features->map(fn (EateryFeature $feature) => [
                'name' => $feature->feature,
                'slug' => $feature->slug,
            ]),
            'opening_times' => $eateryOpeningTimes ? [
                'is_open_now' => $eateryOpeningTimes->is_open_now,
                'today' => [
                    'opens' => $eateryOpeningTimes->opensAt(),
                    'closes' => $eateryOpeningTimes->closesAt(),
                ],
            ] : null,
            'branch' => $branch ? $this->formatBranch($branch) : null,
            'is_nationwide' => $this->county_id === 1,
            'last_updated' => $this->updated_at,
            'last_updated_human' => $this->updated_at?->diffForHumans(),
            'qualifies_for_ai' => $this->reviews->filter(fn (EateryReview $review) => $review->admin_review === false && $review->review)->count() > 0,
        ];
    }

    protected function formatBranch(NationwideBranch $branch): array
    {
        return [
            'id' => $branch->id,
            'name' => $branch->name ?: $this->name,
            'county' => $branch->county?->county,
            'town' => $branch->town?->town,
            'area' => $branch->area?->area,
            'location' => [
                'address' => collect(explode("\n", $branch->address))
                    ->map(fn (string $line) => mb_trim($line))
                    ->join(', '),
                'lat' => $branch->lat,
                'lng' => $branch->lng,
            ],
        ];
    }
}
