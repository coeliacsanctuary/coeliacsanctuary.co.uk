<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop\Product;

use App\DataObjects\BreadcrumbItemData;
use App\Http\Requests\Shop\ProductShowRequest;
use App\Http\Response\Inertia;
use App\Models\Shop\ShopCategory;
use App\Models\Shop\ShopProduct;
use App\Resources\Shop\ShopProductResource;
use App\Resources\Shop\ShopProductReviewResource;
use App\Resources\Shop\ShopTravelCardProductResource;
use App\Schema\FaqSchema;
use App\Support\Helpers;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response as LaravelResponse;
use Illuminate\Support\Facades\Route;
use Inertia\Response;

class ShowController
{
    public function __invoke(ProductShowRequest $request, ShopProduct $product, Inertia $inertia): Response|RedirectResponse
    {
        /** @var \Illuminate\Routing\Route $route */
        $route = Route::getCurrentRoute();

        $rawSlug = $route->originalParameter('product');

        if ($product->legacy_slug === $rawSlug) {
            return redirect(route('shop.product', $product), LaravelResponse::HTTP_MOVED_PERMANENTLY);
        }

        $product->load(['categories', 'prices', 'variants', 'media', 'reviews', 'addOns', 'addOns.prices', 'faqs']);

        if ($product->currentPrices()->isEmpty()) {
            abort(LaravelResponse::HTTP_NOT_FOUND);
        }

        /** @var class-string<JsonResource> $resource */
        $resource = ShopProductResource::class;

        if (Helpers::isTravelCard($product->categories->first())) {
            $resource = ShopTravelCardProductResource::class;

            $product->load(['travelCardSearchTerms' => fn (Relation $builder) => $builder->where('type', 'country')]); /** @phpstan-ignore-line  */
        }

        $reviews = $product->reviews()
            ->with(['parent'])
            ->when($request->float('reviewFilter') > 0, fn (Builder $query) => $query->where('rating', $request->float('reviewFilter')))
            ->latest()
            ->paginate(7);

        /** @var ShopCategory $primaryCategory */
        $primaryCategory = $product->categories->first();

        return $inertia
            ->title($product->title)
            ->metaDescription($product->meta_description)
            ->metaTags(explode(',', $product->meta_keywords))
            ->metaImage($product->social_image)
            ->schema(array_values(array_filter([
                $product->schema()->toScript(),
                $product->faqs->isNotEmpty() ? FaqSchema::make($product->faqs)->toScript() : null,
            ])))
            ->breadcrumbs(collect([
                new BreadcrumbItemData('Coeliac Sanctuary', route('home')),
                new BreadcrumbItemData('Shop', route('shop.index')),
                new BreadcrumbItemData($primaryCategory->title, route('shop.category', $primaryCategory)),
                new BreadcrumbItemData($product->title),
            ]))
            ->render('Shop/Product', [
                'product' => new $resource($product),
                'reviews' => fn () => ShopProductReviewResource::collection($reviews),
                'currentReviewFilter' => $request->float('reviewFilter'),
            ]);
    }
}
