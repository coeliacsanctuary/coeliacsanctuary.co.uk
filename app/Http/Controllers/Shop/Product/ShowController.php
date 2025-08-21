<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop\Product;

use App\Http\Requests\Shop\ProductShowRequest;
use App\Http\Response\Inertia;
use App\Models\Shop\ShopProduct;
use App\Resources\Shop\ShopProductResource;
use App\Resources\Shop\ShopProductReviewResource;
use App\Resources\Shop\ShopTravelCardProductResource;
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

        /** @var class-string<JsonResource> $resource */
        $resource = ShopProductResource::class;

        /** @var string[] | array{string, mixed} $relations */
        $relations = ['categories', 'variants', 'variants.prices', 'media', 'reviews'];

        if ($product->categories->pluck('title')->containsAny(['Coeliac Gluten Free Travel Cards', 'Coeliac+ Other Allergen Travel Cards'])) {
            $resource = ShopTravelCardProductResource::class;
            $relations['travelCardSearchTerms'] = fn (Relation $builder) => $builder->where('type', 'country'); /** @phpstan-ignore-line  */
        }

        $product->load($relations);

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
                'product' => new $resource($product),
                'reviews' => fn () => ShopProductReviewResource::collection($reviews),
                'currentReviewFilter' => $request->float('reviewFilter'),
            ]);
    }
}
