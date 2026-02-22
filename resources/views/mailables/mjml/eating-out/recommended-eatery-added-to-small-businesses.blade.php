@php
    use App\Models\EatingOut\EateryRecommendation;
    use Money\Money;
    /** @var EateryRecommendation $recommendation */

$hideRelatedGap = true;
@endphp

@extends('mailables.mjml.layout')

@push('head')
    <mj-style>
        @media only screen and (min-width: 480px) { *[class~=prod-image] { padding-right: 10px !important;} }
    </mj-style>
@endpush

@section('header')
    <h2 style="padding: 5px 0">Your Eatery Recommendation</h2>
@endsection

@section('main-content')
    <mj-section>
        <mj-column>
            <mj-text mj-class="inner">Hey {{ $recommendation->name }}</mj-text>
            <mj-text mj-class="inner">
                Thanks for your suggestion to add <strong>{{ $recommendation->place_name }}</strong> to the Coeliac Sanctuary Eating
                out guide.
            </mj-text>
            <mj-text mj-class="inner">
                Unfortunately, {{ $recommendation->place_name }} is not elligible to be added to my eating out guide as I can only list eateries where anyone can walk up during business hours to eat.
            </mj-text>
            <mj-text mj-class="inner">
                I have instead added it to my 'Small Business' blog with other small, home businesses and online independent retailers.
            </mj-text>
            <mj-text mj-class="inner" padding-top="10px">
                Thanks, Alison - Coeliac Sanctuary
            </mj-text>
        </mj-column>
    </mj-section>

    <mj-section padding-bottom="20px">
        <mj-column>
            <mj-button href="https://www.coeliacsanctuary.co.uk/blog/gluten-free-small-business-online-independent-retailers-market-stalls-home-bakers">
                Read my Gluten Free Small Business Blog
            </mj-button>
        </mj-column>
    </mj-section>
@endsection
