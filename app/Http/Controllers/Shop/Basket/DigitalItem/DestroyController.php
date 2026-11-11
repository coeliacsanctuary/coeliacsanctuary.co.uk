<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop\Basket\DigitalItem;

use App\Actions\Shop\ResolveBasketAction;
use App\Models\Shop\ShopOrderDigitalItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DestroyController
{
    public function __invoke(Request $request, ShopOrderDigitalItem $digitalItem, ResolveBasketAction $resolveBasketAction): RedirectResponse
    {
        /** @var string | null $token */
        $token = $request->cookie('basket_token');

        $basket = $resolveBasketAction->handle($token, false);

        abort_if( ! $basket, RedirectResponse::HTTP_NOT_FOUND);
        abort_if($digitalItem->order_id !== $basket->id, RedirectResponse::HTTP_NOT_FOUND);

        $digitalItem->delete();

        $digitalItem->order->touch();

        return redirect()->back();
    }
}
