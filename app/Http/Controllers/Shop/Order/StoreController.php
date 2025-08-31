<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop\Order;

use App\Actions\Shop\ApplyDiscountCodeAction;
use App\Actions\Shop\CalculateOrderTotalsAction;
use App\Actions\Shop\Checkout\CreateCustomerAction;
use App\Actions\Shop\Checkout\CreateShippingAddressAction;
use App\Actions\Shop\GetOrderItemsAction;
use App\Actions\Shop\ResolveBasketAction;
use App\DataObjects\Shop\PendingOrderCustomerDetails;
use App\DataObjects\Shop\PendingOrderShippingAddressDetails;
use App\Enums\Shop\OrderState;
use App\Http\Requests\Shop\CompleteOrderRequest;
use App\Models\Shop\ShopDiscountCode;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopPostageCountry;
use App\Resources\Shop\ShopOrderItemResource;
use Exception;
use Illuminate\Encryption\Encrypter;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class StoreController
{
    public function __invoke(
        CompleteOrderRequest $request,
        ResolveBasketAction $resolveBasketAction,
        GetOrderItemsAction $getOrderItemsAction,
        CalculateOrderTotalsAction $calculateOrderTotalsAction,
        CreateCustomerAction $createUserAction,
        CreateShippingAddressAction $createAddressAction,
        ApplyDiscountCodeAction $applyDiscountCodeAction,
    ): Response {
        /** @var string $token */
        $token = $request->cookie('basket_token');

        /** @var ShopOrder $basket */
        $basket = $resolveBasketAction->handle($token, false);

        /** @var ShopPostageCountry $country */
        $country = $basket->postageCountry;

        $items = $getOrderItemsAction->handle($basket);

        /** @var Collection<int, ShopOrderItemResource> $collection */
        $collection = $items->collection;

        try {
            DB::beginTransaction();

            $customer = $createUserAction->handle(PendingOrderCustomerDetails::createFromRequest($request));
            $address = null;

            if ($basket->is_digital_only === false) {
                $address = $createAddressAction->handle(
                    $customer,
                    PendingOrderShippingAddressDetails::createFromRequest($request, $country->country),
                );
            }

            $discount = null;
            ['subtotal' => $subtotal, 'postage' => $postage] = $calculateOrderTotalsAction->handle($basket, $collection, $country);
            $total = $subtotal + $postage;

            if ($request->session()->has('discountCode')) {
                try {
                    /** @var string $discountCodeSession */
                    $discountCodeSession = $request->session()->get('discountCode');

                    $discountCodeString = app(Encrypter::class)->decrypt($discountCodeSession);

                    $discountCode = ShopDiscountCode::query()->where('code', $discountCodeString)->firstOrFail();

                    $discount = $applyDiscountCodeAction->handle($discountCode, $token);

                    $total -= ($discount ?? 0);

                    $discountCode->used()->create([
                        'order_id' => $basket->id,
                        'discount_amount' => $discount,
                    ]);
                } catch (Exception) {
                    //
                }
            }

            $basket->payment()->updateOrCreate([], [
                'subtotal' => $subtotal,
                'postage' => $postage,
                'discount' => $discount ?? 0,
                'total' => $total,
                'created_at' => now(),
            ]);

            $basket->update([
                'customer_id' => $customer->id,
                'shipping_address_id' => $address?->id,
                'order_key' => Str::of(Str::password(8, letters: false, symbols: false))->padLeft(8, '0'),
                'state_id' => OrderState::PENDING,
            ]);

            DB::commit();

            return new Response(status: Response::HTTP_CREATED);
        } catch (Exception $exception) {
            DB::rollBack();

            throw ValidationException::withMessages(['order' => 'There was an error completing your order, you have not been charged']);
        }
    }
}
