<?php

declare(strict_types=1);

namespace App\Listeners\Shop;

use App\Events\Shop\OrderPaidEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Stripe\StripeClient;

class SyncStripeData implements ShouldQueue
{
    public function __construct(protected StripeClient $stripeClient)
    {
    }

    public function handle(OrderPaidEvent $event): void
    {
        $order = $event->order;

        $payment = $order->payment;

        if ( ! $payment) {
            return;
        }

        $paymentResponse = $payment->response;

        if ( ! $paymentResponse || ! $paymentResponse->charge_id) {
            return;
        }

        $this->stripeClient->charges->update(
            $paymentResponse->charge_id,
            [
                'metadata' => [
                    'order_key' => $order->order_key,
                    'order_id' => $order->id,
                    'customer_id' => $order->customer_id,
                    'customer_name' => $order->customer?->name,
                ],
            ],
        );
    }
}
