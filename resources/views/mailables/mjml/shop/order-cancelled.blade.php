@php
    use App\Models\Shop\ShopOrder;
    /** @var ShopOrder $order */
@endphp

@extends('mailables.mjml.shop.layout')

@section('header')
    Your order has been cancelled.
@endsection

@section('intro')
    <mj-text mj-class="inner">Hi {{ $order->address->name }}</mj-text>
    <mj-text mj-class="inner">
        Your recent order at Coeliac Sanctuary has been cancelled.
    </mj-text>
    <mj-text mj-class="inner">
        You will receive your refund separately.
    </mj-text>
    <mj-text mj-class="inner" padding-top="10px">
        Thanks, Alison - Coeliac Sanctuary
    </mj-text>
@endsection
