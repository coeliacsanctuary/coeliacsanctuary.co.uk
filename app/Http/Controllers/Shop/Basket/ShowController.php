<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop\Basket;

use App\Actions\Shop\ApplyDiscountCodeAction;
use App\Actions\Shop\CalculateOrderTotalsAction;
use App\Actions\Shop\CheckForPendingOrderAction;
use App\Actions\Shop\CreatePaymentIntentAction;
use App\Actions\Shop\GetOrderItemsAction;
use App\Actions\Shop\ResolveBasketAction;
use App\Http\Response\Inertia;
use App\Models\Shop\ShopDiscountCode;
use App\Models\Shop\ShopPostageCountry;
use App\Resources\Shop\ShopOrderItemResource;
use App\Support\Helpers;
use Exception;
use Illuminate\Encryption\Encrypter;
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
        CalculateOrderTotalsAction $calculateOrderTotalsAction,
        ApplyDiscountCodeAction $applyDiscountCodeAction,
    ): Response {
        /** @var string $token */
        $token = $request->cookies->get('basket_token');
        $basket = $resolveBasketAction->handle($token, false);

        if ( ! $basket) {
            $basket = $checkForPendingOrderAction->handle($token);
        }

        $props = [
            'has_basket' => false,
        ];

        if ($basket && $basket->items()->count() > 0) {
            $basket->touch();

            /** @var ShopPostageCountry $country */
            $country = $basket->postageCountry;

            $items = $getOrderItemsAction->handle($basket);

            /** @var Collection<int, ShopOrderItemResource> $collection */
            $collection = $items->collection;

            ['subtotal' => $subtotal, 'postage' => $postage, 'fees' => $fees, 'total_fees' => $totalFees] = $calculateOrderTotalsAction->handle($collection, $country);

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
                    'items' => $items,
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
            ->doNotTrack()
            ->render('Shop/Checkout', $props);
    }
}
