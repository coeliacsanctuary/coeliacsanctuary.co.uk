<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop\Category;

use App\DataObjects\BreadcrumbItemData;
use App\Http\Response\Inertia;
use App\Models\Shop\ShopCategory;
use App\Models\Shop\ShopProduct;
use App\Resources\Shop\ShopCategoryIndexResource;
use App\Resources\Shop\ShopProductIndexResource;
use App\Schema\ShopCategoryProductListSchema;
use App\Support\Helpers;
use Illuminate\Database\Eloquent\Relations\Relation;
use Inertia\Response;

class ShowController
{
    public function __invoke(ShopCategory $category, Inertia $inertia): Response
    {
        /** @var string[] | array{string, mixed} $relations */
        $relations = ['media', 'variants', 'prices', 'reviews'];

        if (Helpers::isTravelCard($category)) {
            $relations['travelCardSearchTerms'] = fn (Relation $query) => $query->where('type', 'country'); /** @phpstan-ignore-line */
        }

        $products = $category->products()
            ->with($relations)
            ->orderByDesc('pinned')
            ->orderBy('title')
            ->get()
            /** @phpstan-ignore-next-line */
            ->filter(fn (ShopProduct $product) => $product->currentPrice !== null)
            ->map(fn (ShopProduct $product) => $product->setRelation('categories', collect([$category])))
            ->values();

        return $inertia
            ->title($category->title)
            ->metaDescription($category->meta_description)
            ->metaTags(explode(',', $category->meta_keywords))
            ->metaImage($category->social_image)
            ->schema(ShopCategoryProductListSchema::make($category, $products)->toScript())
            ->breadcrumbs(collect([
                new BreadcrumbItemData('Coeliac Sanctuary', route('home')),
                new BreadcrumbItemData('Shop', route('shop.index')),
                new BreadcrumbItemData($category->title),
            ]))
            ->render('Shop/Category', [
                'category' => new ShopCategoryIndexResource($category),
                'products' => ShopProductIndexResource::collection($products),
            ]);
    }
}
