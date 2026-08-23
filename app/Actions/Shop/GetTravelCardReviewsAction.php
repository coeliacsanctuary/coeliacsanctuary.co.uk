<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Models\Shop\ShopOrderReviewItem;
use App\Resources\Shop\ShopIndexReviewResource;
use Carbon\CarbonInterval;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class GetTravelCardReviewsAction
{
    /** @return array{reviews: AnonymousResourceCollection, count: int, average: float|null} */
    public function handle(): array
    {
        /** @var array{reviews: AnonymousResourceCollection, count: int, average: float|null} $summary */
        $summary = Cache::flexible(
            'travel-card-reviews',
            [CarbonInterval::hours(6), CarbonInterval::minutes(30)],
            fn () => [
                'reviews' => ShopIndexReviewResource::collection(
                    $this->travelCardReviews()
                        ->where('rating', '>=', 4)
                        ->whereNotNull('review')
                        ->where('review', '!=', '')
                        ->whereHas('parent')
                        ->with(['parent', 'product'])
                        ->latest()
                        ->take(6)
                        ->get()
                ),
                'count' => $this->travelCardReviews()->count(),
                'average' => $this->averageRating(),
            ],
        );

        return $summary;
    }

    /** @return EloquentBuilder<ShopOrderReviewItem> */
    protected function travelCardReviews(): EloquentBuilder
    {
        return ShopOrderReviewItem::query()
            ->whereHas('product', fn (Builder $query) => $query->whereHas(
                'categories',
                fn (Builder $query) => $query->whereIn('shop_categories.id', [1, 11]),
            ));
    }

    protected function averageRating(): ?float
    {
        $average = $this->travelCardReviews()->avg('rating');

        return $average === null ? null : round((float) $average, 1);
    }
}
