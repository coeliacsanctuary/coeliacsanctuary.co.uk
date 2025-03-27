<?php

declare(strict_types=1);

namespace App\Resources\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryAttractionRestaurant;
use App\Models\EatingOut\EateryCuisine;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EateryReviewImage;
use App\Models\EatingOut\EateryType;
use App\Models\EatingOut\EateryVenueType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Eatery */
class EateryAppResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request)
    {
        $reviews = $this->relationLoaded('reviews') ? $this->reviews->loadMissing('images')->map(function (EateryReview $review) {
            $review->refresh();

            return [
                'id' => $review->id,
                'admin_review' => $review->admin_review,
                'wheretoeat_id' => $review->wheretoeat_id,
                'rating' => $review->rating,
                'name' => $review->name,
                'body' => $review->review,
                'created_at' => $review->created_at,
                'price' => $review->price,
                'human_date' => $review->created_at->diffForHumans(),
                'images' => $review->images->map(fn (EateryReviewImage $image) => [
                    'id' => $image->id,
                    'thumb' => $image->thumb,
                    'path' => $image->path,
                ]),
            ];
        }) : [];

        /** @var EateryCuisine $cuisine */
        $cuisine = $this->cuisine;

        /** @var EateryVenueType $eateryVenueType */
        $eateryVenueType = $this->venueType;

        /** @var EateryType $eateryType */
        $eateryType = $this->type;

        $branch = $this->relationLoaded('branch') ? $this->branch : null;

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'town_id' => $this->town_id,
            'county_id' => $this->county_id,
            'country_id' => $this->country_id,
            'info' => $this->info,
            'address' => $branch->address ?? $this->address,
            'phone' => $this->phone,
            'website' => $this->website,
            'gf_menu_link' => $this->gf_menu_link,
            'lat' => $branch->lat ?? $this->lat,
            'lng' => $branch->lng ?? $this->lng,
            'type_id' => $this->type_id,
            'venue_type_id' => $this->venue_type_id,
            'cuisine_id' => $this->cuisine_id,
            'live' => $this->live,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'ratings' => $reviews,
            'is_nationwide' => $this->county_id > 1,
            'unique_key' => $this->id . ($branch ? '-' . $branch->id : null),
            'average_rating' => $this->average_rating,
            'has_been_rated' => $this->has_been_rated,
            'icon' => '', // todo
            'full_name' => $this->full_name,
            'full_location' => $this->full_location,
            'type_description' => $this->typeDescription,
            'country' => [
                'id' => $branch->country_id ?? $this->country_id,
                'country' => $branch->country->country ?? $this->country?->country,
            ],
            'county' => [
                'id' => $branch->county_id ?? $this->county_id,
                'county' => $branch->county?->county ?? $this->county?->county,
                'slug' => $branch->county?->slug ?? $this->county?->slug,
            ],
            'town' => [
                'id' => $branch->town_id ?? $this->town_id,
                'town' => $branch->town?->town ?? $this->town?->town,
                'slug' => $branch->town?->slug ?? $this->town?->slug,
            ],
            'type' => [
                'id' => $eateryType->id,
                'type' => $eateryType->type,
                'name' => $eateryType->name,
            ],
            'venue_type' => [
                'id' => $eateryVenueType->id,
                'venue_type' => $eateryVenueType->venue_type,
            ],
            'cuisine' => [
                'id' => $cuisine->id,
                'cuisine' => $cuisine->cuisine,
            ],
            'features' => $this->relationLoaded('features') ? $this->features->map(fn (EateryFeature $feature) => [
                'id' => $feature->id,
                'feature' => $feature->feature,
                // icon?
                // image?
            ]) : [],
            'restaurants' => $this->restaurants->map(fn (EateryAttractionRestaurant $restaurant): array => [
                'name' => $restaurant->restaurant_name,
                'info' => $restaurant->info,
            ]),
            'user_reviews' => $reviews,
            'distance' => $branch->distance ?? $this->distance,
        ];
    }
}
