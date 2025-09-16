@php
    use App\Models\Shop\ShopOrder;
    /** @var ShopOrder $order */

$minimal = true;
$hideRelatedGap = true;
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
        <mj-column >
            <mj-button padding-top="20px" font-size="20px" padding-bottom="30px" href="{{ $downloadLink }}">
                Download Your Products!
            </mj-button>

            <mj-text mj-class="small" align="center" padding-top="0">Link not working? Copy this into your browser.</mj-text>
            <mj-text mj-class="small" align="center" padding-top="5px" font-weight="bold">{{ $downloadLink }}</mj-text>
            <mj-text mj-class="small" align="center" padding-top="15px">
                Please note this link will expire in one month, if you need to download your products again after this time, please contact us with your order number above.
            </mj-text>
        </mj-column>
    </mj-section>
@endsection
