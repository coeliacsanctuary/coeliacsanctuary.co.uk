<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop\Basket;

use App\Actions\Shop\CheckIfBasketHasDigitalProductsAction;
use App\Actions\Shop\ResolveBasketAction;
use App\Enums\Shop\ProductVariantType;
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

        $item->delete();

        if ($item->variant?->variant_type !== ProductVariantType::DIGITAL) {
            $item->variant?->increment('quantity', $item->quantity);
        }

        $basket->touch();

        app(CheckIfBasketHasDigitalProductsAction::class)->handle($basket);

        return redirect()->back();
    }
}
