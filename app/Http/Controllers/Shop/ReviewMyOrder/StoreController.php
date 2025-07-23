<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop\ReviewMyOrder;

use App\Http\Requests\Shop\ReviewMyOrderRequest;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderReview;
use App\Models\Shop\ShopOrderReviewInvitation;
use App\Models\Shop\ShopOrderReviewItem;
use App\Models\Shop\ShopSource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;

class StoreController
{
    public function __invoke(ShopOrderReviewInvitation $invitation, ReviewMyOrderRequest $request): RedirectResponse
    {
        abort_if($invitation->review()->count() > 0, 404);

        /** @var ShopOrder $order */
        $order = $invitation->order;

        /** @var Collection<int, string> $whereHeard */
        $whereHeard = $request->collect('whereHeard');

        $whereHeard
            ->map(fn (string $whereHeard) => ShopSource::query()->firstOrCreate(['source' => $whereHeard]))
            ->each(fn (ShopSource $source) => $source->orders()->attach($order));

        /** @var ShopOrderReview $review */
        $review = $invitation->review()->create([
            'order_id' => $invitation->order_id,
            'name' => $request->string('name')->toString(),
        ]);

        /** @var Collection<int, array{id: int, rating: int, review: string}> $products */
        $products = $request->collect('products');

        $products->each(fn (array $product) => $review->products()->create([
            'product_id' => $product['id'],
            'order_id' => $invitation->order_id,
            'rating' => $product['rating'],
            'review' => $product['review'],
        ]));

        $review->products->load('product')->each(function (ShopOrderReviewItem $orderReviewItem): void {
            $orderReviewItem->product?->sealiacOverview?->update([
                'invalidated' => true,
            ]);
        });

        return new RedirectResponse(route('shop.review-order.thanks', $invitation));
    }
}
