<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use App\Resources\Shop\ShopPopularProductResource;
use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;

class GetBestSellingProductsForShopIndexAction
{
    public function handle(): AnonymousResourceCollection
    {
        /** @var AnonymousResourceCollection $products */
        $products = Cache::flexible(
            'shop-popular-products',
            [CarbonInterval::hours(6), CarbonInterval::minutes(30)],
            fn () => ShopPopularProductResource::collection($this->resolveProducts())
        );

        return $products;
    }

    /** @return Collection<int, ShopProduct> */
    protected function resolveProducts(): Collection
    {
        /** @var int $windowDays */
        $windowDays = config('coeliac.shop.popular_products_window_days');

        /** @var array<int, int> $productIds */
        $productIds = ShopOrderItem::query()
            ->select('product_id')
            ->selectRaw('SUM(quantity) as total_sold')
            ->whereHas('order', fn ($query) => $query->whereIn('state_id', [OrderState::PAID, OrderState::READY, OrderState::SHIPPED]))
            ->whereRelation('order', 'created_at', '>=', Carbon::now()->subDays($windowDays))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit(12)
            ->pluck('product_id')
            ->all();

        if ($productIds === []) {
            return new Collection();
        }

        $products = ShopProduct::query()
            ->whereIn('id', $productIds)
            ->whereRelation('variants', 'quantity', '>', 0)
            ->with(['media', 'prices', 'reviews'])
            ->get()
            ->sortBy(fn (ShopProduct $product) => array_search($product->id, $productIds, true))
            ->values();

        return $products->take($this->fullRowCount(min($products->count(), 6)));
    }

    protected function fullRowCount(int $available): int
    {
        if ($available < 3) {
            return $available;
        }

        return intdiv($available, 3) * 3;
    }
}
