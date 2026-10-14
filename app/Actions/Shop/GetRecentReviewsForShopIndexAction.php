<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopOrderReviewItem;
use App\Models\Shop\ShopProduct;
use App\Resources\Shop\ShopIndexReviewResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class GetRecentReviewsForShopIndexAction
{
    public function handle(): AnonymousResourceCollection
    {
        /** @var string $key */
        $key = config('coeliac.cacheable.shop-reviews.index');

        /** @var AnonymousResourceCollection $reviews */
        $reviews = Cache::rememberForever(
            $key,
            fn () => ShopIndexReviewResource::collection(ShopOrderReviewItem::query()
                ->where('rating', '>=', 4)
                ->whereNotNull('review')
                ->where('review', '!=', '')
                ->whereHasMorph('product', [ShopProduct::class, ShopDigitalCard::class])
                ->whereHas('parent')
                ->with(['parent', 'product'])
                ->latest()
                ->take(6)
                ->get())
        );

        return $reviews;
    }
}
