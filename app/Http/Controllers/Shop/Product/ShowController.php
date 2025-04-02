<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop\Product;

use App\Http\Requests\Shop\ProductShowRequest;
use App\Http\Response\Inertia;
use App\Models\Shop\ShopProduct;
use App\Resources\Shop\ShopProductResource;
use App\Resources\Shop\ShopProductReviewResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
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

        $product->load(['categories', 'prices', 'variants', 'media', 'reviews']);

        $reviews = $product->reviews()
            ->with(['parent'])
            ->when($request->float('reviewFilter') > 0, fn (Builder $query) => $query->where('rating', $request->float('reviewFilter')))
            ->latest()
            ->paginate(7);

        return $inertia
            ->title($product->title)
            ->metaDescription($product->meta_description)
            ->metaTags(explode(',', $product->meta_keywords))
            ->metaImage($product->social_image)
            ->schema($product->schema()->toScript())
            ->render('Shop/Product', [
                'product' => new ShopProductResource($product),
                'reviews' => fn () => ShopProductReviewResource::collection($reviews),
                'currentReviewFilter' => $request->float('reviewFilter'),
            ]);
    }
}
