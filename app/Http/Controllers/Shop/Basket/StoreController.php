<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop\Basket;

use App\Actions\Shop\AddDigitalCardToBasketAction;
use App\Actions\Shop\AddProductToBasketAction;
use App\Actions\Shop\ResolveBasketAction;
use App\Http\Requests\Shop\AddToBasketRequest;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopProduct;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Cookie;

class StoreController
{
    public function __invoke(
        AddToBasketRequest $request,
        ResolveBasketAction $resolveBasketAction,
        AddProductToBasketAction $addProductToBasketAction,
        AddDigitalCardToBasketAction $addDigitalCardToBasketAction,
    ): RedirectResponse {
        DB::beginTransaction();

        try {
            /** @var string | null $token */
            $token = $request->cookies->get('basket_token');

            /** @var ShopOrder $order */
            $order = $resolveBasketAction->handle($token);

            $item = null;

            if ($request->isPhysicalProduct()) {
                $product = ShopProduct::query()->findOrFail($request->integer('product_id'));
                $variant = $product->variants()->findOrFail($request->integer('variant_id'));

                $item = $addProductToBasketAction->handle(
                    $order,
                    $product,
                    $variant,
                    $request->integer('quantity'),
                    $request->isDigitalProduct() === false,
                );
            }

            if ($request->isDigitalProduct()) {
                $digitalCard = ShopDigitalCard::query()->findOrFail($request->integer('digital_card_id'));

                $addDigitalCardToBasketAction->handle($order, $digitalCard, $item);
            }

            DB::commit();

            return redirect()
                ->back()
                ->withCookie(new Cookie('basket_token', $order->token, now()->addHour()));
        } catch (Exception $exception) {
            DB::rollBack();

            throw ValidationException::withMessages(['product_id' => 'There was an error adding the item to your basket.']);
        }
    }
}
