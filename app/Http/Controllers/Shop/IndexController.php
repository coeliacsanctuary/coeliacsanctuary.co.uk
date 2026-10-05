<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Actions\OpenGraphImages\GetOpenGraphImageForRouteAction;
use App\Actions\Shop\GetBestSellingProductsForShopIndexAction;
use App\Actions\Shop\GetRecentReviewsForShopIndexAction;
use App\DataObjects\BreadcrumbItemData;
use App\Enums\Shop\DigitalCardType;
use App\Http\Response\Inertia;
use App\Models\Shop\ShopCategory;
use App\Resources\Shop\ShopCategoryIndexResource;
use App\Schema\ShopCategoryListSchema;
use App\Support\Helpers;
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
        $allCategories = ShopCategory::query()
            ->with(['media', 'products' => fn (Relation $query) => $query->withCount('reviews')->with('prices')])
            ->withCount('products')
            ->orderBy('title')
            ->get();

        $travelCardCategories = $allCategories->filter(fn(ShopCategory $category) => Helpers::isTravelCard($category));
        $everythingElse = $allCategories->reject(fn(ShopCategory $category) => Helpers::isTravelCard($category));

        return $inertia
            ->title('Gluten Free Travel Translation Cards & Stickers')
            ->metaDescription('Shop gluten free translation cards in over 60 languages and coeliac stickers. Designed to help you communicate your dietary needs and travel with confidence.')
            ->metaTags([
                'Gluten free merchandise', 'coeliac sanctuary shop', 'coeliac travel cards', 'gluten free travel cards',
                'travelling abroad', 'gluten free abroad', 'coeliac travel', 'gluten free travel',
                'gluten free stickers', 'gluten free waterproof stickers', 'coeliac shop',
            ])
            ->metaImage($getOpenGraphImageForRouteAction->handle('shop'))
            ->schema(ShopCategoryListSchema::make($allCategories)->toScript())
            ->breadcrumbs(collect([
                new BreadcrumbItemData('Coeliac Sanctuary', route('home')),
                new BreadcrumbItemData('Shop'),
            ]))
            ->render('Shop/Index', [
                'categories' => [
                    'travelCards' => ShopCategoryIndexResource::collection($travelCardCategories),
                    'everythingElse' => ShopCategoryIndexResource::collection($everythingElse),
                ],
                'digitalCards' => DigitalCardType::toShopIndexArray(),
                'popularProducts' => fn () => $getBestSellingProductsForShopIndexAction->handle(),
                'reviews' => fn () => $getRecentReviewsForShopIndexAction->handle(),
            ]);
    }
}
