<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Enums\Shop\OrderState;
use App\Exceptions\OrderAlreadyPaidException;
use App\Models\Shop\ShopOrder;
use Exception;
use Illuminate\Support\Facades\Log;
use Stripe\PaymentIntent;

class CheckForPendingOrderAction
{
    public function __construct(protected GetPaymentIntentAction $getPaymentIntentAction)
    {
        //
    }

    /** @throws OrderAlreadyPaidException */
    public function handle(?string $token = null): ?ShopOrder
    {
        if ( ! $token) {
            return null;
        }

        $order = ShopOrder::query()
            ->where('token', $token)
            ->where('state_id', OrderState::PENDING)
            ->first();

        if ( ! $order) {
            return null;
        }

        if ($this->hasBeenPaidFor($order)) {
            throw new OrderAlreadyPaidException($order);
        }

        $order->update(['state_id' => OrderState::BASKET]);

        return $order;
    }

    protected function hasBeenPaidFor(ShopOrder $order): bool
    {
        if ( ! $order->payment_intent_id) {
            return false;
        }

        try {
            $intent = $this->getPaymentIntentAction->handle($order->payment_intent_id);

            return $intent->status === PaymentIntent::STATUS_SUCCEEDED;
        } catch (Exception $exception) {
            Log::error('Unable to check payment intent for pending order - ' . $exception->getMessage(), [
                'order_id' => $order->id,
            ]);

            return false;
        }
    }
}
