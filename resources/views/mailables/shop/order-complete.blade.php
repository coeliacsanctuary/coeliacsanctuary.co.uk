@php
    use App\Models\Shop\ShopOrder;
    use App\Support\Helpers;
    use Illuminate\Support\Str;
    use Money\Money;
    /** @var ShopOrder $order */
@endphp

@extends('mailables.layout')

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
        <mj-column >
            <mj-text mj-class="inner">Hey {{ $order->address->name }}</mj-text>
            <mj-text mj-class="inner">
                Thanks a bunch for your recent order at the Coeliac Sanctuary shop! Check out the details of your order below.
            </mj-text>
            <mj-text mj-class="inner" padding-top="10px">
                Thanks, Alison - Coeliac Sanctuary
            </mj-text>
        </mj-column>
    </mj-section>

    <mj-section mj-class="light-section">
        <mj-column>
            <mj-text align="center" padding-bottom="10px"><h1>Order Summary</h2></mj-text>
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

    <!-- BEGIN PRODUCTS -->
    @foreach($order->items as $item)
        <mj-section margin-bottom="{{ $loop->last ? '0' : '10px' }}">
            <mj-column width="25%" padding="4px 0">
                <mj-image
                    src="{{ $item->product->main_image }}"
                    fluid-on-mobile="true"
                    css-class="prod-image"
                />
            </mj-column>
            <mj-column width="60%" padding="4px 0">
                <mj-text font-size="18px" line-height="1.2">
                    <a href="{{ $item->product->link }}">{{ $item->quantity }}X {{ $item->product_title }}</a>
                </mj-text>
            </mj-column>
            <mj-column width="15%" padding="4px 0">
                <mj-text align="right">{{ Helpers::formatMoney(Money::GBP($item->product_price)) }}</mj-text>
            </mj-column>
        </mj-section>
    @endforeach
    <!-- END: PRODUCTS -->

    <mj-section>
        <mj-column>
            <mj-text>
                <hr color="#DBBC25"></hr>
            </mj-text>
        </mj-column>
    </mj-section>

    <!-- BEGIN: TOTALS -->
    <mj-section>
        <mj-column css-class="force-half-width" width="50%"><mj-text line-height="1.5">Subtotal</mj-text></mj-column>
        <mj-column css-class="force-half-width" width="50%"><mj-text line-height="1.5" align="right">{{ Helpers::formatMoney(Money::GBP($order->payment->subtotal)) }}</mj-text></mj-column>

        @if($order->payment->discount)
        <mj-column css-class="force-half-width" width="50%"><mj-text line-height="1.5">Discount</mj-text></mj-column>
        <mj-column css-class="force-half-width" width="50%"><mj-text line-height="1.5" align="right">{{ Helpers::formatMoney(Money::GBP($order->payment->discount)) }}</mj-text></mj-column>
        @endif

        <mj-column css-class="force-half-width" width="50%"><mj-text line-height="1.5">Postage</mj-text></mj-column>
        <mj-column css-class="force-half-width" width="50%"><mj-text line-height="1.5" align="right">{{ Helpers::formatMoney(Money::GBP($order->payment->postage)) }}</mj-text></mj-column>

        <mj-column css-class="force-half-width" width="50%"><mj-text line-height="1.5" padding-top="10px"><h2>Total</h2></mj-text></mj-column>
        <mj-column css-class="force-half-width" width="50%"><mj-text line-height="1.5" align="right" padding-top="10px"><h2>{{ Helpers::formatMoney(Money::GBP($order->payment->total)) }}</h2></mj-text></mj-column>
    </mj-section>
    <!-- END: TOTALS -->

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
