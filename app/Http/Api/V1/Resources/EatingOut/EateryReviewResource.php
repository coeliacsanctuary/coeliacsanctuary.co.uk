<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Resources\EatingOut;

use App\Models\EatingOut\EateryReview;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin EateryReview */ class EateryReviewResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'name' => $this->name,
            'published' => $this->created_at,
            'date_diff' => $this->human_date,
            'body' => $this->review,
            'rating' => (float) $this->rating,
            'expense' => $this->price,
            'food_rating' => $this->food_rating,
            'service_rating' => $this->service_rating,
            'branch_name' => $this->branch_name,
            'images' => $this->whenLoaded('images', fn () => EateryReviewImageResource::collection($this->images)),
        ];
    }
}
