<?php

declare(strict_types=1);

namespace App\Resources\EatingOut;

use App\Models\EatingOut\EateryReview;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin EateryReview */
class SimpleReviewResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request)
    {
        return [
            'rating' => $this->rating,
            'eatery' => new SimpleEateryResource($this->branch ?? $this->eatery),
            'created_at' => $this->created_at->diffForHumans(),
        ];
    }
}
