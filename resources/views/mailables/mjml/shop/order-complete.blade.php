@php
    use App\Models\Shop\ShopOrder;
    /** @var ShopOrder $order */
@endphp

@extends('mailables.mjml.shop.layout')

@section('header')
    Thank you for your order!
@endsection

@section('intro')
    <mj-text mj-class="inner">Hey {{ $order->address->name }}</mj-text>
    <mj-text mj-class="inner">
        Thanks a bunch for your recent order at the Coeliac Sanctuary shop! Check out the details of your order
        below.
    </mj-text>
    <mj-text mj-class="inner" padding-top="10px">
        Thanks, Alison - Coeliac Sanctuary
    </mj-text>
@endsection
