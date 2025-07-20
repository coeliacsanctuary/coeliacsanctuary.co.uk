@php
    use App\Models\Shop\ShopOrder;
    /** @var ShopOrder $order */
@endphp

@extends('mailables.mjml.shop.layout')

@section('header')
    Your order is on its way!
@endsection

@section('intro')
    <mj-text mj-class="inner">Hey {{ $order->address->name }}</mj-text>
    <mj-text mj-class="inner">
        Good news! Your recent order at the Coeliac Sanctuary shop has been dispatched and is on its way to you!
    </mj-text>
    <mj-text mj-class="inner">
        The details of your order can be found below.
    </mj-text>
    <mj-text mj-class="inner" padding-top="10px">
        Thanks again, Alison - Coeliac Sanctuary
    </mj-text>
@endsection
