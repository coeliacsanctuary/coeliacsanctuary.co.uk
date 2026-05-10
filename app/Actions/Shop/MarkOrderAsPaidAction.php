<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\Enums\Shop\OrderState;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopPayment;
use Illuminate\Support\Str;
use RuntimeException;
use Stripe\BalanceTransaction;
use Stripe\Charge;
use Stripe\PaymentMethod;

class MarkOrderAsPaidAction
{
    public function handle(ShopOrder $pendingOrder, Charge $stripeCharge): void
    {
        if ($pendingOrder->state_id !== OrderState::PENDING) {
            throw new RuntimeException('Order must be pending');
        }

        $pendingOrder->update([
            'state_id' => OrderState::PAID,
        ]);

        /** @var ShopPayment $payment */
        $payment = $pendingOrder->payment;

        /** @var PaymentMethod $paymentMethod */
        $paymentMethod = PaymentMethod::constructFrom($stripeCharge->payment_method_details?->toArray() ?? []);

        /** @var BalanceTransaction $balanceTransaction */
        $balanceTransaction = $stripeCharge->balance_transaction;

        $payment->update([
            'payment_type_id' => $this->getPaymentType($paymentMethod),
            'fee' => $balanceTransaction->fee,
        ]);

        $payment->response()->updateOrCreate([], [
            'charge_id' => $stripeCharge->id,
            'response' => $stripeCharge->toArray(),
        ]);
    }

    protected function getPaymentType(PaymentMethod $paymentMethod): string
    {
        if ($paymentMethod->type === 'paypal') {
            return 'PayPal';
        }

        if (isset($paymentMethod->card->wallet->type)) {
            return Str::headline($paymentMethod->card->wallet->type);
        }

        return 'Card';
    }
}
