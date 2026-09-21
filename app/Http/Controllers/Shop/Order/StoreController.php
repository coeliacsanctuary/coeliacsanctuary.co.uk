<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop\Order;

use App\Actions\Shop\CalculateOrderTotalsAction;
use App\Actions\Shop\Checkout\CreateCustomerAction;
use App\Actions\Shop\Checkout\CreateShippingAddressAction;
use App\Actions\Shop\GetOrderDigitalItemsAction;
use App\Actions\Shop\GetOrderItemsAction;
use App\Actions\Shop\ResolveDiscountForOrderAction;
use App\DataObjects\Shop\PendingOrderCustomerDetails;
use App\DataObjects\Shop\PendingOrderShippingAddressDetails;
use App\Enums\Shop\OrderState;
use App\Http\Requests\Shop\CompleteOrderRequest;
use App\Models\Shop\ShopPostageCountry;
use App\Resources\Shop\ShopOrderDigitalItemResource;
use App\Resources\Shop\ShopOrderItemResource;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class StoreController
{
    public function __invoke(
        CompleteOrderRequest $request,
        GetOrderItemsAction $getOrderItemsAction,
        GetOrderDigitalItemsAction $getOrderDigitalItemsAction,
        CalculateOrderTotalsAction $calculateOrderTotalsAction,
        CreateCustomerAction $createUserAction,
        CreateShippingAddressAction $createAddressAction,
        ResolveDiscountForOrderAction $resolveDiscountForOrderAction,
    ): Response {
        $basket = $request->basket();

        /** @var ShopPostageCountry $country */
        $country = $basket->postageCountry;

        $physicalItems = $getOrderItemsAction->handle($basket);
        $digitalItems = $getOrderDigitalItemsAction->handle($basket);

        /** @var Collection<int, ShopOrderItemResource> $physicalItemCollection */
        $physicalItemCollection = $physicalItems->collection;

        /** @var Collection<int, ShopOrderDigitalItemResource> $digitalItemCollection */
        $digitalItemCollection = $digitalItems->collection;

        ['subtotal' => $subtotal, 'postage' => $postage, 'fees' => $fees, 'total_fees' => $totalFees] = $calculateOrderTotalsAction->handle($physicalItemCollection, $digitalItemCollection, $country);
        $total = $subtotal + $postage + $totalFees;

        $discountCode = null;
        $discount = null;

        $isDigitalOnly = $physicalItemCollection->isEmpty();

        if ($request->session()->has('discountCode')) {
            /** @var string $discountCodeSession */
            $discountCodeSession = $request->session()->get('discountCode');

            try {
                [$discountCode, $discount] = $resolveDiscountForOrderAction->handle($discountCodeSession, $basket->token);
            } catch (RuntimeException) {
                $request->session()->forget('discountCode');

                throw ValidationException::withMessages([
                    'basket' => 'Your discount code is no longer valid, and your total has been updated - please review your basket and try again.',
                ]);
            }
        }

        $total -= ($discount ?? 0);

        try {
            DB::beginTransaction();

            $customer = $createUserAction->handle(PendingOrderCustomerDetails::createFromRequest($request));

            if ( ! $isDigitalOnly) {
                $address = $createAddressAction->handle(
                    $customer,
                    PendingOrderShippingAddressDetails::createFromRequest($request, $country->country),
                );
            }

            $discountCode?->used()->updateOrCreate(
                ['order_id' => $basket->id],
                ['discount_amount' => $discount],
            );

            $basket->payment()->updateOrCreate([], [
                'subtotal' => $subtotal,
                'postage' => $postage,
                'discount' => $discount ?? 0,
                'fees_breakdown' => $fees,
                'custom_fees' => $totalFees,
                'total' => $total,
                'created_at' => now(),
            ]);

            $basket->update([
                'customer_id' => $customer->id,
                'shipping_address_id' => $address->id ?? null,
                'order_key' => Str::of(Str::password(8, letters: false, symbols: false))->padLeft(8, '0'),
                'state_id' => OrderState::PENDING,
                'newsletter_signup' => $request->boolean('contact.subscribeToNewsletter'),
                'has_digital_items' => $digitalItemCollection->isNotEmpty(),
                'is_digital_only' => $isDigitalOnly,
            ]);

            DB::commit();

            return new Response(status: Response::HTTP_CREATED);
        } catch (Exception $exception) {
            Log::critical('checkout', [
                'exception' => $exception->getMessage(),
                'trace' => $exception->getTrace(),
            ]);

            DB::rollBack();

            throw ValidationException::withMessages(['order' => 'There was an error completing your order, you have not been charged']);
        }
    }
}
