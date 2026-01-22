<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop\Basket\AddOn;

use App\Actions\Shop\ResolveBasketAction;
use App\Models\Shop\ShopOrderItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DestroyController
{
    public function __invoke(Request $request, ShopOrderItem $item, ResolveBasketAction $resolveBasketAction): RedirectResponse
    {
        /** @var string | null $token */
        $token = $request->cookie('basket_token');

        $basket = $resolveBasketAction->handle($token, false);

        abort_if( ! $basket, RedirectResponse::HTTP_NOT_FOUND);
        abort_if($item->order_id !== $basket->id, RedirectResponse::HTTP_NOT_FOUND);
        abort_if(!$item->product_add_on_id, RedirectResponse::HTTP_NOT_FOUND);
        abort_if(!$item->product->addOns, RedirectResponse::HTTP_NOT_FOUND);
        abort_if($item->product->addOns->id !== $item->product_add_on_id, RedirectResponse::HTTP_NOT_FOUND);

        $item->update([
            'product_add_on_id' => null,
            'product_add_on_title' => null,
            'product_add_on_price' => null,
        ]);

        $item->order?->touch();

        return redirect()->back();
    }
}
