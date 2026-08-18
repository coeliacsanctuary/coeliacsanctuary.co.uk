<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Actions\OpenGraphImages\GetOpenGraphImageForRouteAction;
use App\Actions\Shop\GetBestSellingProductsForShopIndexAction;
use App\Actions\Shop\GetRecentReviewsForShopIndexAction;
use App\DataObjects\BreadcrumbItemData;
use App\Http\Response\Inertia;
use App\Models\Shop\ShopCategory;
use App\Resources\Shop\ShopCategoryIndexResource;
use App\Schema\ShopCategoryListSchema;
use Illuminate\Database\Eloquent\Relations\Relation;
use Inertia\Response;

class IndexController
{
    public function __invoke(
        Inertia $inertia,
        GetOpenGraphImageForRouteAction $getOpenGraphImageForRouteAction,
        GetBestSellingProductsForShopIndexAction $getBestSellingProductsForShopIndexAction,
        GetRecentReviewsForShopIndexAction $getRecentReviewsForShopIndexAction,
    ): Response {
        $categories = ShopCategory::query()
            ->with(['media', 'products' => fn (Relation $query) => $query->withCount('reviews')->with('prices')])
            ->withCount('products')
            ->orderBy('title')
            ->get();

        return $inertia
            ->title('Gluten Free Travel Translation Cards & Stickers')
            ->metaDescription('Shop gluten free translation cards in over 60 languages and coeliac stickers. Designed to help you communicate your dietary needs and travel with confidence.')
            ->metaTags([
                'Gluten free merchandise', 'coeliac sanctuary shop', 'coeliac travel cards', 'gluten free travel cards',
                'travelling abroad', 'gluten free abroad', 'coeliac travel', 'gluten free travel',
                'gluten free stickers', 'gluten free waterproof stickers', 'coeliac shop',
            ])
            ->metaImage($getOpenGraphImageForRouteAction->handle('shop'))
            ->schema(ShopCategoryListSchema::make($categories)->toScript())
            ->breadcrumbs(collect([
                new BreadcrumbItemData('Coeliac Sanctuary', route('home')),
                new BreadcrumbItemData('Shop'),
            ]))
            ->render('Shop/Index', [
                'categories' => ShopCategoryIndexResource::collection($categories),
                'popularProducts' => fn () => $getBestSellingProductsForShopIndexAction->handle(),
                'reviews' => fn () => $getRecentReviewsForShopIndexAction->handle(),
            ]);
    }
}
