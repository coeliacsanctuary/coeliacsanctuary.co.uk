<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Actions\OpenGraphImages\GetOpenGraphImageForRouteAction;
use App\DataObjects\BreadcrumbItemData;
use App\Http\Response\Inertia;
use App\Models\Shop\ShopCategory;
use App\Resources\Shop\ShopCategoryIndexResource;
use Inertia\Response;

class IndexController
{
    public function __invoke(Inertia $inertia, GetOpenGraphImageForRouteAction $getOpenGraphImageForRouteAction): Response
    {
        $categories = ShopCategory::query()
            ->with(['media'])
            ->orderBy('title')
            ->get();

        return $inertia
            ->title('Shop - Travel Cards, Wristbands and more')
            ->metaDescription('Coeliac Sanctuary gluten free and coeliac travel cards, wristbands, stickers and other helpful products for Coeliacs.')
            ->metaTags([
                'Gluten free merchandise', 'coeliac sanctuary shop', 'coeliac travel cards', 'gluten free travel cards',
                'travelling abroad', 'gluten free abroad', 'coeliac travel', 'gluten free travel', 'coeliac wristbands',
                'gluten free stickers', 'gluten free wristbands', 'gluten free waterproof stickers', 'coeliac shop',
            ])
            ->metaImage($getOpenGraphImageForRouteAction->handle('shop'))
            ->breadcrumbs(collect(array_filter([
                new BreadcrumbItemData('Coeliac Sanctuary', route('home')),
                new BreadcrumbItemData('Shop'),
            ])))
            ->render('Shop/Index', [
                'categories' => ShopCategoryIndexResource::collection($categories),
            ]);
    }
}
