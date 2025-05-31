@php
    use App\Models\Shop\ShopOrder;
    use App\Models\Shop\ShopOrderItem;use App\Support\Helpers;
    use Illuminate\Support\Str;
    use Money\Money;
    /** @var ShopOrder $basket */
@endphp

@extends('mailables.mjml.layout')

@push('head')
    <mj-style>
        @media only screen and (min-width: 480px) { *[class~=prod-image] { padding-right: 10px !important;} }
    </mj-style>
@endpush

@section('header')
    <h2 style="padding: 5px 0">Did you forget to checkout?</h2>
@endsection

@section('main-content')
    <mj-section>
        <mj-column>
            <mj-text mj-class="inner">Hey {{ $basket->customer->name }}</mj-text>
            <mj-text mj-class="inner">
                Oops – did you forget something? Your basket’s still hanging around! You've still got chance to complete your order!
            </mj-text>
            <mj-text mj-class="inner" padding-top="10px">
                Thanks, Alison - Coeliac Sanctuary
            </mj-text>
        </mj-column>
    </mj-section>

    <mj-section>
        <mj-column>
            <mj-button href="{{ $link }}">
                Checkout Now!
            </mj-button>
        </mj-column>
    </mj-section>

    <mj-section>
        <mj-column>
            <mj-text mj-class="inner">
                <h2>Your Basket</h2>
            </mj-text>
        </mj-column>
    </mj-section>

    <!-- BEGIN PRODUCTS -->
    @foreach($basket->items as $item)
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
            <mj-button href="{{ $link }}">
                Checkout Now!
            </mj-button>
        </mj-column>
    </mj-section>
@endsection
