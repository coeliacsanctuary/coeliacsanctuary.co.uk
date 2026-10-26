<?php

declare(strict_types=1);

namespace App\Actions\Shop\FinanceService;

use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDigitalItem;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopPayment;
use App\Models\Shop\ShopPaymentResponse;
use App\Models\Shop\ShopShippingAddress;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;

class SendOrderToFinanceServiceAction
{
    public function handle(ShopOrder $order): void
    {
        $order->loadMissing(['customer', 'address', 'payment', 'payment.response', 'items', 'digitalItems']);

        /** @var ShopPayment $payment */
        $payment = $order->payment;

        /** @var ShopPaymentResponse $paymentResponse */
        $paymentResponse = $payment->response;

        /** @var ShopShippingAddress $address */
        $address = $order->address;

        /** @var ShopCustomer $customer */
        $customer = $order->customer;

        /** @var Collection<int, ShopOrderItem> $items */
        $items = $order->items;

        /** @var Collection<int, ShopOrderDigitalItem> $digitalItems */
        $digitalItems = $order->digitalItems;

        $addressArray = [];

        if ( ! $order->is_digital_only) {
            $addressArray = [
                'address_1' => $address->line_1,
                'address_2' => $address->line_2,
                'address_3' => $address->line_3,
                'town' => $address->town,
                'county' => $address->county,
                'postcode' => $address->postcode,
                'country' => $address->country,
            ];
        }

        $payload = [
            'order_id' => $order->token,
            'ordered_at' => $paymentResponse->created_at->toIso8601String(),
            'stripe_charge_id' => $paymentResponse->charge_id,
            'customer' => [
                'name' => $customer->name,
                'email' => $customer->email,
                ...$addressArray
            ],
            'items' => [
                ...$items->map(fn (ShopOrderItem $item) => [
                    'id' => $item->product_id,
                    'variant_id' => $item->product_variant_id,
                    'name' => $item->product_title,
                    'quantity' => $item->quantity,
                    'line_price' => $item->product_price,
                    'total_price' => $item->product_price * $item->quantity,
                ])->toArray(),
                ...$digitalItems->map(fn (ShopOrderDigitalItem $item) => [
                    'id' => $item->digital_card_id,
                    'variant_id' => null,
                    'name' => $item->digital_card_title,
                    'quantity' => 1,
                    'line_price' => $item->digital_card_price,
                    'total_price' => $item->digital_card_price,
                ])->toArray(),
            ],
            'subtotal' => $payment->subtotal,
            'postage' => $payment->postage,
            'fees' => collect($payment->fees_breakdown)->map(fn (array $fee) => [
                'name' => $fee['description'],
                'amount' => $fee['fee'],
            ])->toArray(),
            'discounts' => $order->discountCode ? [[
                'name' => $order->discountCode->name,
                'code' => $order->discountCode->code,
                'amount' => $payment->discount,
            ]] : null,
            'total' => $payment->total,
        ];

        Http::financeService()->post('/api/v1/orders', $payload);
    }
}
