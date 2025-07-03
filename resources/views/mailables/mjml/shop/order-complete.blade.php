@php
    use App\Models\Shop\ShopOrder;
    use App\Support\Helpers;
    use Illuminate\Support\Str;
    use Money\Money;
    /** @var ShopOrder $order */
@endphp

@extends('mailables.mjml.layout')

@push('head')
    <mj-style>
        @media only screen and (min-width: 480px) { *[class~=prod-image] { padding-right: 10px !important;} }
    </mj-style>
@endpush

@section('header')
    <h2 style="padding: 5px 0">Thank you for your order!</h2>
@endsection

@section('main-content')
    <mj-section>
        <mj-column>
            <mj-text mj-class="inner">Hey {{ $order->address->name }}</mj-text>
            <mj-text mj-class="inner">
                Thanks a bunch for your recent order at the Coeliac Sanctuary shop! Check out the details of your order
                below.
            </mj-text>
            <mj-text mj-class="inner" padding-top="10px">
                Thanks, Alison - Coeliac Sanctuary
            </mj-text>
        </mj-column>
    </mj-section>

    <mj-section mj-class="light-section">
        <mj-column>
            <mj-text align="center" padding-bottom="10px"><h2>Order Summary</h2></mj-text>
            <mj-text align="center" padding-bottom="4px">
                <strong>Order Number:</strong> {{ $order->order_key }}<br/>
            </mj-text>
            <mj-text align="center">
                <strong>Order Total:</strong> {{ Helpers::formatMoney(Money::GBP($order->payment->total)) }}<br/>
            </mj-text>
        </mj-column>
    </mj-section>

    <mj-section>
        <mj-column>
            <mj-text mj-class="inner">
                <h2>Order Details</h2>
            </mj-text>
        </mj-column>
    </mj-section>

    <x-mjml-shop-order-table :order="$order" />

    <mj-section>
        <mj-column>
            <mj-text>
                <hr color="#DBBC25"></hr>
            </mj-text>
        </mj-column>
    </mj-section>

    <mj-section>
        <mj-column>
            <mj-text><h2>Postage Address</h2></mj-text>
            <mj-text mj-class="inner">
                <span style="line-height:1.4">
                    {!! Str::markdown($order->address->formattedAddress, ['renderer' => ['soft_break' => ", "]]) !!}
                </span>
            </mj-text>
        </mj-column>
    </mj-section>
@endsection
