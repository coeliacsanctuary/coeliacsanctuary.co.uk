@php
    use App\Models\Shop\ShopOrder;
    /** @var ShopOrder $order */

$minimal = true;
@endphp

@extends('mailables.mjml.shop.layout')

@section('header')
    Your products are ready!
@endsection

@section('intro')
    <mj-text mj-class="inner">Hey {{ $order->address->name ?? $order->customer->name }}</mj-text>
    <mj-text mj-class="inner">
        Your digital products for order <strong>{{ $order->order_key }}</strong> are ready to download, click the link below now to download them!
    </mj-text>
    <mj-text mj-class="inner" padding-top="10px">
        Thanks, Alison - Coeliac Sanctuary
    </mj-text>
@endsection

@section('footer')
    <mj-section mj-class="light-section">
        <mj-column>
            <mj-button href="{{ $downloadLink }}">
                Download Your Products!
            </mj-button>

            <mj-text>Link not working? Copy this into your browser.</mj-text>
            <mj-text padding-top="10px" font-weight="bold">{{ $downloadLink }}</mj-text>
        </mj-column>
    </mj-section>
@endsection
