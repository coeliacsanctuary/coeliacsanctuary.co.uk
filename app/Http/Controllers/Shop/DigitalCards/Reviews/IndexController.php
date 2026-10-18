<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop\DigitalCards\Reviews;

use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopOrderReviewItem;
use App\Resources\Shop\ShopProductReviewResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class IndexController
{
    public function __invoke(ShopDigitalCard $digitalCard, Request $request): array
    {
        $reviews = $digitalCard->reviews()
            ->with(['parent'])
            ->when($request->float('reviewFilter') > 0, fn (Builder $query) => $query->where('rating', $request->float('reviewFilter')))
            ->latest()
            ->paginate(7)
            ->withQueryString()
            ->through(fn (ShopOrderReviewItem $review) => ShopProductReviewResource::make($review));

        return [
            'data' => [
                'rating' => [
                    'average' => $digitalCard->averageRating,
                    'count' => $digitalCard->reviews->count(),
                    'breakdown' => collect(range(5, 1))->map(fn ($rating) => [
                        'rating' => $rating,
                        'count' => $digitalCard->reviews->filter(fn (ShopOrderReviewItem $reviewItem) => (int) $reviewItem->rating === $rating)->count(),
                    ]),
                ],
                'reviews' => $reviews,
            ]
        ];
    }
}
