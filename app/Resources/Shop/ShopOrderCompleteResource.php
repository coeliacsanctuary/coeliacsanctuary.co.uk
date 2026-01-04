<?php

declare(strict_types=1);

namespace App\Resources\Shop;

use App\Models\Shop\ShopDiscountCode;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopPayment;
use App\Models\Shop\ShopShippingAddress;
use App\Support\Helpers;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Money\Money;
use Stripe\Card;
use Stripe\PaymentMethod;

/** @mixin ShopOrder */
class ShopOrderCompleteResource extends JsonResource
{
    public function __construct($resource, protected PaymentMethod $paymentMethod)
    {
        parent::__construct($resource);
    }

    public function toArray(Request $request): array
    {
        /** @var ShopPayment $payment */
        $payment = $this->payment;

        /** @var ShopShippingAddress $shipping */
        $shipping = $this->address;

        /** @var ShopDiscountCode | null $discount */
        $discount = $this->discountCode;

        return [
            'id' => $this->order_key,
            'products' => ShopOrderItemResource::collection($this->items),
            'subtotal' => Helpers::formatMoney(Money::GBP($payment->subtotal)),
            'fees' => Arr::map($payment->fees_breakdown ?? [], fn (array $fee) => [
                ... $fee,
                'fee' => Helpers::formatMoney(Money::GBP($fee['fee']))
            ]),
            'total_fees' => Helpers::formatMoney(Money::GBP($payment->custom_fees)),
            'discount' => $payment->discount && $discount ? [
                'amount' => Helpers::formatMoney(Money::GBP($payment->discount)),
                'name' => $discount->name,
            ] : null,
            'postage' => Helpers::formatMoney(Money::GBP($payment->postage)),
            'total' => Helpers::formatMoney(Money::GBP($payment->total)),
            'shipping' => array_filter([
                $shipping->name,
                $shipping->line_1,
                $shipping->line_2,
                $shipping->line_3,
                $shipping->town,
                $shipping->county,
                $shipping->postcode,
                $shipping->country,
            ]),
            'payment' => $this->getPaymentDetails((string) $payment->payment_type_id),
            'event' => [
                'transaction_id' => $this->order_key,
                'value' => $payment->subtotal / 100,
                'currency' => 'GBP',
                'shipping' => $payment->postage / 100,
                'tax' => 0,
                'affiliation' => 'Coeliac Sanctuary',
                'items' => $this->items->map(fn (ShopOrderItem $item) => [
                    'id' => $item->product_id,
                    'sku' => "{$item->product_id} - {$item->product_variant_id}",
                    'name' => $item->product_title,
                    'variant' => $item->product_variant_id,
                    'quantity' => $item->quantity,
                    'price' => $item->product_price / 100,
                ]),
            ],
        ];
    }

    protected function getPaymentDetails(string $paymentType): array
    {
        $base = ['type' => $paymentType];

        if ($paymentType === 'Card') {
            /** @var Card $card */
            $card = $this->paymentMethod->card;

            $base = [
                ...$base,
                'lastDigits' => $card->last4,
                'expiry' => Str::of((string) $card->exp_month)->padLeft(2, '0')
                    ->append(' / ')
                    ->append(Str::of((string) $card->exp_year)->reverse()->take(2)->reverse()->toString()),
            ];
        }

        if ($paymentType === 'PayPal') {
            /** @var object{payer_email: string} $paypal */
            $paypal = $this->paymentMethod->paypal;

            $base = [
                ...$base,
                'paypalAccount' => $paypal->payer_email,
            ];
        }

        return $base;
    }
}
