@php
    use App\Models\Shop\ShopCustomer;
    use App\Support\Helpers;
    use Illuminate\Support\Str;
    use Money\Money;
    /** @var ShopCustomer $customer */
@endphp

@extends('mailables.mjml.layout')

@push('head')
    <mj-style>
        @media only screen and (min-width: 480px) { *[class~=prod-image] { padding-right: 10px !important;} }
    </mj-style>
@endpush

@section('header')
    <h2 style="padding: 5px 0">Here's your one time code!</h2>
@endsection

@section('main-content')
    <mj-section>
        <mj-column>
            <mj-text mj-class="inner">Hey {{ $customer->name }}</mj-text>
            <mj-text mj-class="inner">
                Here's your one time code to access your travel cards.
            </mj-text>
            <mj-text mj-class="inner" font-weight="bold">
                {{ $code }}
            </mj-text>
            <mj-text mj-class="inner" padding-top="10px">
                Thanks, Alison - Coeliac Sanctuary
            </mj-text>
        </mj-column>
    </mj-section>
@endsection
