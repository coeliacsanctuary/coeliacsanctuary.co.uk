<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop\ReviewMyOrder;

use App\Actions\Shop\GetOrderProductsForReviewAction;
use App\Http\Response\Inertia;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderReviewInvitation;
use Illuminate\Http\RedirectResponse;
use Inertia\Response;

class ShowController
{
    public function __invoke(
        Inertia $inertia,
        ShopOrderReviewInvitation $invitation,
        GetOrderProductsForReviewAction $getOrderProductsForReviewAction,
    ): RedirectResponse|Response {
        if ($invitation->review()->count() > 0) {
            return new RedirectResponse(route('shop.review-order.thanks', $invitation));
        }

        /** @var ShopOrder $order */
        $order = $invitation->order;

        /** @var ShopCustomer $customer */
        $customer = $order->customer;

        return $inertia
            ->title('Review My Order')
            ->doNotTrack()
            ->render('Shop/ReviewMyOrder', [
                'id' => $order->order_key,
                'invitation' => $invitation->id,
                'name' => $customer->name,
                'products' => $getOrderProductsForReviewAction->handle($order),
            ]);
    }
}
