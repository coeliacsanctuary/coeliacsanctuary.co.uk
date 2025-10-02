<?php

declare(strict_types=1);

namespace App\Actions\EatingOut;

use App\Jobs\EatingOut\ProcessReviewImagesJob;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryReview;
use Illuminate\Support\Arr;

class CreateEateryReviewAction
{
    public function handle(Eatery $eatery, array $data): EateryReview
    {
        /** @var array | null $images */
        $images = Arr::get($data, 'images');
        unset($data['images']);

        $review = $eatery->reviews()->create($data);

        if ($images) {
            ProcessReviewImagesJob::dispatch($review, $images);
        }

        if ($review->approved) {
            $eatery->touch();
        }

        return $review;
    }
}
