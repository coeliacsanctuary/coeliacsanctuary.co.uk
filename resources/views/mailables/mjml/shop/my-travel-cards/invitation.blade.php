@php
    use App\Models\Shop\ShopOrder;
    /** @var ShopOrder $order */

$minimal = true;
$hideRelatedGap = true;
@endphp

@extends('mailables.mjml.shop.layout')

@section('header')
    Access your Digital Travel Cards!
@endsection

@section('intro')
    <mj-text mj-class="inner">Hey {{ $order->customer->name }}</mj-text>
    <mj-text mj-class="inner">
        Your digital travel cards for order <strong>{{ $order->order_key }}</strong> are ready to access,
        click the link below now to get started!
    </mj-text>
    <mj-text mj-class="inner" padding-top="10px">
        Thanks, Alison - Coeliac Sanctuary
    </mj-text>
@endsection

@section('footer')
    <mj-section mj-class="light-section">
        <mj-column >
            <mj-button padding-top="20px" font-size="20px" padding-bottom="30px" href="{{ $downloadLink }}">
                Access Your Digital Travel Cards
            </mj-button>

            <mj-text mj-class="small" align="center" padding-top="0">Link not working? Copy this into your browser.</mj-text>
            <mj-text mj-class="small" align="center" padding-top="5px" font-weight="bold">{{ $downloadLink }}</mj-text>
            <mj-text mj-class="small" align="center" padding-top="15px">
                Please note this link will expire in one week, you can access your digital cards anytime by going to
                <a href="{{ route('my-travel-cards.index') }}">{{ route('my-travel-cards.index') }}</a>
                and entering your email address.
            </mj-text>
        </mj-column>
    </mj-section>
@endsection
