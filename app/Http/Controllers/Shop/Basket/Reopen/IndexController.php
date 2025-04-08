<?php

namespace App\Http\Controllers\Shop\Basket\Reopen;

use App\Actions\Shop\ReopenBasketAction;
use App\Enums\Shop\OrderState;
use App\Http\Response\Inertia;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopProduct;
use App\Resources\Shop\ShopProductIndexResource;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response as IlluminateResponse;
use Inertia\Response;

class IndexController{
    public function __invoke(Request $request, ShopOrder $basket, ReopenBasketAction $reopenBasketAction): Response | RedirectResponse
    {
        if(!$request->hasValidSignature()) {
            $randomProducts = ShopProduct::query()
                ->with(['prices', 'reviews', 'variants'])
                ->take(6)
                ->inRandomOrder()
                ->get();

            return app(Inertia::class)
                ->title('Link Expired')
                ->metaTags([], false)
                ->doNotTrack()
                ->render('Shop/LinkExpired', [
                    'products' => ShopProductIndexResource::collection($randomProducts),
                ]);
        }

        abort_if($basket->state_id !== OrderState::EXPIRED || $basket->sent_abandoned_basket_email === false, IlluminateResponse::HTTP_NOT_FOUND);

        $warnings = $reopenBasketAction->handle($basket);

        return redirect()
            ->route('shop.basket.checkout')
            ->withCookie('basket_token', $basket->token)
            ->with('basket_warnings', $warnings->toArray());
    }
}
