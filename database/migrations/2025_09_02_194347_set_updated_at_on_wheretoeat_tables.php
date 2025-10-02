<?php

declare(strict_types=1);

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryReview;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up(): void
    {
        if (app()->runningUnitTests()) {
            return;
        }

        Eatery::query()
            ->whereHas('reviews')
            ->with(['reviews' => fn ($query) => $query->latest()])
            ->lazy()
            ->each(function ($eatery): void {
                /** @var EateryReview $review */
                $review = $eatery->reviews->first();

                $eatery->updateQuietly(['updated_at' => $review->created_at]);
            });
    }
};
