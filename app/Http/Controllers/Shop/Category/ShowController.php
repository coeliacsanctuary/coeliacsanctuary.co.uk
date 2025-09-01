<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop\Category;

use App\DataObjects\BreadcrumbItemData;
use App\Http\Response\Inertia;
use App\Models\Shop\ShopCategory;
use App\Resources\Shop\ShopCategoryIndexResource;
use App\Resources\Shop\ShopProductIndexResource;
use Inertia\Response;

class ShowController
{
    public function __invoke(ShopCategory $category, Inertia $inertia): Response
    {
        $products = $category->products()
            ->with(['media', 'variants', 'prices', 'reviews'])
            ->orderByDesc('pinned')
            ->orderBy('title')
            ->get();

        return $inertia
            ->title($category->title)
            ->metaDescription($category->meta_description)
            ->metaTags(explode(',', $category->meta_keywords))
            ->metaImage($category->social_image)
            ->breadcrumbs(collect(array_filter([
                new BreadcrumbItemData('Coeliac Sanctuary', route('home')),
                new BreadcrumbItemData('Shop', route('shop.index')),
                new BreadcrumbItemData($category->title),
            ])))
            ->render('Shop/Category', [
                'category' => new ShopCategoryIndexResource($category),
                'products' => ShopProductIndexResource::collection($products),
            ]);
    }
}
