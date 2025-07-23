<?php

declare(strict_types=1);

namespace App\Resources\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryAttractionRestaurant;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryOpeningTimes;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EateryReviewImage;
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
        /** @var Collection<int, EateryReview> $reviews */
        $reviews = $this->reviews;

        /** @var Collection<int, EateryFeature> $features */
        $features = $this->features;

        /** @var NationwideBranch | null $branch */
        $branch = $this->relationLoaded('branch') ? $this->branch : null;

        /** @var EateryReview | null $adminReview */
        $adminReview = $this->adminReview;

        /** @var EateryOpeningTimes | null $eateryOpeningTimes */
        $eateryOpeningTimes = $this->openingTimes;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'closed_down' => $this->closed_down,
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
            'area' => $this->area ? [
                'id' => $this->area_id,
                'name' => $this->area->area,
                'link' => $this->area->link(),
            ] : null,
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
                'number' => $this->reviews->count(),
                'average' => $this->average_rating,
                'expense' => $this->average_expense,
                'has_rated' => $this->has_been_rated,
                'images' => $this->approvedReviewImages->count() > 0 ? $this->approvedReviewImages->map(fn (EateryReviewImage $image) => [
                    'id' => $image->id,
                    'thumbnail' => $image->thumb,
                    'path' => $image->path,
                    'location' => $image->review->branch && $image->review->branch->name ? "{$image->review->branch->name}, {$image->review->branch->town?->town}" : $image->review->eatery?->name,
                ]) : [],
                'admin_review' => $adminReview ? [
                    'published' => $adminReview->created_at,
                    'date_diff' => $adminReview->human_date,
                    'body' => $adminReview->review,
                    'rating' => (float) $adminReview->rating,
                    'expense' => $adminReview->price,
                    'food_rating' => $adminReview->food_rating,
                    'service_rating' => $adminReview->service_rating,
                    'branch_name' => $adminReview->branch_name,
                    'images' => $adminReview->images->count() > 0 ? $adminReview->images->map(fn (EateryReviewImage $image) => [
                        'id' => $image->id,
                        'thumbnail' => $image->thumb,
                        'path' => $image->path,
                    ]) : [],
                ] : null,
                'user_reviews' => $reviews->map(fn (EateryReview $review) => [
                    'id' => $review->id,
                    'published' => $review->created_at,
                    'date_diff' => $review->human_date,
                    'name' => $review->name,
                    'body' => $review->review,
                    'rating' => (float) $review->rating,
                    'expense' => $review->price,
                    'food_rating' => $review->food_rating,
                    'service_rating' => $review->service_rating,
                    'branch_name' => $review->branch ? $review->branch->name : $review->branch_name,
                    'images' => $review->images->count() > 0 ? $review->images->map(fn (EateryReviewImage $image) => [
                        'id' => $image->id,
                        'thumbnail' => $image->thumb,
                        'path' => $image->path,
                    ]) : [],
                ]),
                'ratings' => collect(range(5, 1))->map(fn ($rating) => [
                    'rating' => $rating,
                    'count' => $reviews->filter(fn (EateryReview $reviewItem) => (int) $reviewItem->rating === $rating)->count(),
                ]),
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
                'days' => $eateryOpeningTimes->opening_times_array,
            ] : null,
            'branch' => $branch ? $this->formatBranch($branch) : null,
            'is_nationwide' => $this->county_id === 1,
            'nationwide_branches' => $this->getBranchList(),
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
            'county' => [
                'id' => $branch->county_id,
                'name' => $branch->county?->county,
                'link' => $branch->county?->link(),
            ],
            'town' => [
                'id' => $branch->town_id,
                'name' => $branch->town?->town,
                'link' => $branch->town?->link(),
            ],
            'area' => $branch->area ? [
                'id' => $branch->area_id,
                'name' => $branch->area->area,
                'link' => $branch->area->link(),
            ] : null,
            'link' => $branch->link(),
            'location' => [
                'address' => collect(explode("\n", $branch->address))
                    ->map(fn (string $line) => mb_trim($line))
                    ->join(', '),
                'lat' => $branch->lat,
                'lng' => $branch->lng,
            ],
        ];
    }

    protected function getBranchList(): ?array
    {
        if ( ! $this->relationLoaded('nationwideBranches')) {
            return null;
        }

        return $this->nationwideBranches
            ->groupBy(fn (NationwideBranch $branch) => $branch->country->country) /** @phpstan-ignore-line */
            ->sortKeys()
            ->map(
                fn (Collection $branches) => $branches
                    ->groupBy(fn (NationwideBranch $branch) => $branch->county->county) /** @phpstan-ignore-line */
                    ->sortKeys()
                    ->map(
                        fn (Collection $branches) => $branches
                            ->groupBy(fn (NationwideBranch $branch) => $branch->town->town) /** @phpstan-ignore-line */
                            ->sortKeys()
                            ->map(
                                fn (Collection $branches) => $branches
                                    ->groupBy(fn (NationwideBranch $branch) => $branch->area?->area ?? '_') /** @phpstan-ignore-line */
                                    ->sortKeys()
                                    ->map(
                                        fn (Collection $branches) => $branches
                                            ->sortBy('name')
                                            ->map(fn (NationwideBranch $branch) => $this->formatBranch($branch))
                                    )
                            )
                    )
            )
            ->toArray();
    }
}
