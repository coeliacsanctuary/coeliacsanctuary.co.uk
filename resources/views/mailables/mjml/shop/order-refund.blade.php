@php
    use App\Models\Shop\ShopOrder;
    use App\Models\Shop\ShopPaymentRefund;
    use App\Support\Helpers;
    use Money\Money;
    /** @var ShopOrder $order */
    /** @var ShopPaymentRefund $refund */
@endphp

@extends('mailables.mjml.shop.layout')

@section('header')
    Your order has received a refund.
@endsection

@section('intro')
    <mj-text mj-class="inner">Hi {{ $order->address->name }}</mj-text>
    <mj-text mj-class="inner">
        Your Coeliac Sanctuary order #{{ $order->order_key }} has received a refund of {{ Helpers::formatMoney(Money::GBP($refund->amount))  }}
    </mj-text>
    @if($refundReason)
        <mj-text mj-class="inner">The reason given for your refund was:</mj-text>
        <mj-text mj-class="inner"><strong>{{ $refundReason }}</strong></mj-text>
    @endif
    <mj-text mj-class="inner" padding-top="10px">
        Thanks, Alison - Coeliac Sanctuary
    </mj-text>
@endsection
