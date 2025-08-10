<?php

declare(strict_types=1);

namespace App\Actions\EatingOut;

use App\Models\EatingOut\EateryReview;
use App\Resources\EatingOut\SimpleReviewResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class GetLatestReviewsForHomepageAction
{
    public function handle(): AnonymousResourceCollection
    {
        /** @var string $key */
        $key = config('coeliac.cacheable.eating-out-reviews.home');

        /** @var AnonymousResourceCollection $reviews */
        $reviews = Cache::rememberForever(
            $key,
            fn () => SimpleReviewResource::collection(EateryReview::query()
                /** @phpstan-ignore-next-line  */
                ->whereHas('eatery', fn (Builder $builder) => $builder->where('live', true))
                ->with([
                    'eatery', 'eatery.area', 'eatery.town', 'eatery.county', 'eatery.country', 'eatery.town.county',
                    'branch', 'branch.area', 'branch.town', 'branch.county', 'branch.country', 'branch.eatery',
                ])
                ->take(8)
                ->latest()
                ->get())
        );

        return $reviews;
    }
}
