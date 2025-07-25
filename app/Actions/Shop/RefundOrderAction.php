<?php

declare(strict_types=1);

namespace App\Actions\Shop;

use App\DataObjects\Shop\RefundOrderDto;
use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopPayment;
use App\Models\Shop\ShopPaymentResponse;
use App\Notifications\Shop\OrderRefundNotification;
use Stripe\Refund;
use Stripe\StripeClient;

class RefundOrderAction
{
    public function __construct(protected StripeClient $stripeClient)
    {
        //
    }

    public function handle(ShopOrder $order, RefundOrderDto $refundOrderDto): void
    {
        /** @var ShopPayment $payment */
        $payment = $order->payment;

        /** @var ShopPaymentResponse $response */
        $response = $payment->response;

        $chargeId = $response->charge_id;

        /** @var null | Refund  $refund */
        $refund = null;

        if ($chargeId) {
            $refund = $this->stripeClient->refunds->create([
                'charge' => $chargeId,
                'amount' => $refundOrderDto->amount,
            ]);
        }

        $refundModel = $payment->refunds()->create([
            'order_id' => $order->id,
            'refund_id' => $refund?->id,
            'amount' => $refundOrderDto->amount,
            'note' => $refundOrderDto->note,
            'response' => $refund?->toJSON(),
            'created_at' => $refundOrderDto->createdAt,
            'updated_at' => $refundOrderDto->createdAt,
        ]);

        if ($refundOrderDto->cancelOrder) {
            app(CancelOrderAction::class)->handle($order);
        }

        if ($refundOrderDto->notifyCustomer) {
            /** @var ShopCustomer $customer */
            $customer = $order->customer;

            $customer->notify(new OrderRefundNotification($refundModel, $refundOrderDto->reason));
        }
    }
}
