<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop\Basket;

use App\Actions\Shop\ApplyDiscountCodeAction;
use App\Actions\Shop\CalculateOrderTotalsAction;
use App\Actions\Shop\CheckForPendingOrderAction;
use App\Actions\Shop\CreatePaymentIntentAction;
use App\Actions\Shop\GetBestSellingProductsForShopIndexAction;
use App\Actions\Shop\GetOrderDigitalItemsAction;
use App\Actions\Shop\GetOrderItemsAction;
use App\Actions\Shop\ResolveBasketAction;
use App\Exceptions\OrderAlreadyPaidException;
use App\Http\Response\Inertia;
use App\Models\Shop\ShopDiscountCode;
use App\Models\Shop\ShopPostageCountry;
use App\Resources\Shop\ShopOrderDigitalItemResource;
use App\Resources\Shop\ShopOrderItemResource;
use App\Support\Helpers;
use Exception;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Inertia\Response;
use Money\Money;

class ShowController
{
    public function __invoke(
        Inertia $inertia,
        Request $request,
        ResolveBasketAction $resolveBasketAction,
        CheckForPendingOrderAction $checkForPendingOrderAction,
        GetOrderItemsAction $getOrderItemsAction,
        GetOrderDigitalItemsAction $getOrderDigitalItemsAction,
        CalculateOrderTotalsAction $calculateOrderTotalsAction,
        ApplyDiscountCodeAction $applyDiscountCodeAction,
    ): Response|RedirectResponse {
        /** @var string $token */
        $token = $request->cookies->get('basket_token');
        $basket = $resolveBasketAction->handle($token, false);

        if ( ! $basket) {
            try {
                $basket = $checkForPendingOrderAction->handle($token);
            } catch (OrderAlreadyPaidException $exception) {
                return redirect()->route('shop.basket.done', [
                    'payment_intent' => $exception->order->payment_intent_id,
                    'payment_intent_client_secret' => $exception->order->payment_intent_secret,
                ]);
            }
        }

        $props = [
            'has_basket' => false,
            'popularProducts' => fn () => app(GetBestSellingProductsForShopIndexAction::class)->handle(),
        ];

        if ($basket?->hasAnyItems()) {
            $basket->touch();

            /** @var ShopPostageCountry $country */
            $country = $basket->postageCountry;

            $physicalItems = $getOrderItemsAction->handle($basket, false, true);
            $digitalItems = $getOrderDigitalItemsAction->handle($basket);

            /** @var Collection<int, ShopOrderItemResource> $physicalItemCollection */
            $physicalItemCollection = $physicalItems->collection;

            /** @var Collection<int, ShopOrderDigitalItemResource> $digitalItemCollection */
            $digitalItemCollection = $digitalItems->collection;

            ['subtotal' => $subtotal, 'postage' => $postage, 'fees' => $fees, 'total_fees' => $totalFees] = $calculateOrderTotalsAction->handle($physicalItemCollection, $digitalItemCollection, $country);

            $total = $subtotal + $postage + $totalFees;

            $discount = null;

            if ($request->session()->has('discountCode')) {
                try {
                    /** @var string $discountCodeSession */
                    $discountCodeSession = $request->session()->get('discountCode');

                    $discountCodeString = app(Encrypter::class)->decrypt($discountCodeSession);

                    $discountCode = ShopDiscountCode::query()->where('code', $discountCodeString)->firstOrFail();

                    $discount = $applyDiscountCodeAction->handle($discountCode, $token);

                    $total -= ($discount ?? 0);
                } catch (Exception $exception) {
                    $request->session()->forget('discountCode');
                }
            }

            $props = [
                'has_basket' => true,
                'countries' => fn () => ShopPostageCountry::query()
                    ->orderBy('country')
                    ->get()
                    ->map(fn (ShopPostageCountry $postageCountry) => [
                        'value' => $postageCountry->id,
                        'label' => $postageCountry->country,
                    ]),
                'basket' => fn () => [
                    'items' => $physicalItems,
                    'digital_items' => $digitalItems,
                    'digital_only' => $physicalItemCollection->isEmpty(),
                    'selected_country' => $basket->postage_country_id,
                    'delivery_timescale' => $basket->postageCountry?->area?->delivery_timescale,
                    'subtotal' => Helpers::formatMoney(Money::GBP($subtotal)),
                    'postage' => Helpers::formatMoney(Money::GBP($postage)),
                    'discount' => $discount ? Helpers::formatMoney(Money::GBP($discount)) : null,
                    'fees' => $fees->map(fn (array $fee) => [
                        ...$fee,
                        'fee' => Helpers::formatMoney(Money::GBP($fee['fee'])),
                    ]),
                    'total_fees' => Helpers::formatMoney(Money::GBP($totalFees)),
                    'total' => Helpers::formatMoney(Money::GBP($total)),
                ],
                'payment_intent' => app(CreatePaymentIntentAction::class)->handle($basket, $total),
                'warnings' => $request->session()->get('basket_warnings', []),
            ];
        }

        return $inertia
            ->title('Checkout')
            ->disableAds()
            ->doNotTrack()
            ->render('Shop/Checkout', $props);
    }
}
