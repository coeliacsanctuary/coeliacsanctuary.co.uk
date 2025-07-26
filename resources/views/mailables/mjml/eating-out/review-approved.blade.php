@php
    use App\Models\EatingOut\EateryReview;
    use App\Support\Helpers;
    use Illuminate\Support\Str;
    use Money\Money;
    /** @var EateryReview $eateryReview */
@endphp

@extends('mailables.mjml.layout')

@push('head')
    <mj-style>
        @media only screen and (min-width: 480px) { *[class~=prod-image] { padding-right: 10px !important;} }
    </mj-style>
@endpush

@section('header')
    <h2 style="padding: 5px 0">Review Approved!</h2>
@endsection

@section('main-content')
    <mj-section>
        <mj-column>
            <mj-text mj-class="inner">Hey {{ $eateryReview->name }}</mj-text>
            <mj-text mj-class="inner">
                Your recent review of <strong>{{ $eateryReview->eatery->name }}</strong> on Coeliac Sanctuary has been approved!
                Thank you for sharing your thoughts with the wider community and helping others eat out safely!
            </mj-text>
            <mj-text mj-class="inner" padding-top="10px">
                Thanks, Alison - Coeliac Sanctuary
            </mj-text>
        </mj-column>
    </mj-section>

    <mj-section>
        <mj-column>
            <mj-text mj-class="inner">
                <h2 style="text-align: center">Your Review</h2>
            </mj-text>
        </mj-column>
    </mj-section>

    <mj-section padding="0 25px 0 25px">
        <mj-column mj-class="blue" padding="25px">
            <mj-text mj-class="inner">
                {{ $eateryReview->review }}
            </mj-text>
        </mj-column>
    </mj-section>

    <mj-section padding="0 25px">
        <mj-column mj-class="light-blue" width="25%" padding="5px 10px">
            <mj-text mj-class="inner">
                <strong>Your Rating</strong>
            </mj-text>
        </mj-column>
        <mj-column height="100%" mj-class="light-section" padding="5px 10px" width="75%">
            <mj-text mj-class="inner">
                {{ $eateryReview->rating }} Stars
            </mj-text>
        </mj-column>
    </mj-section>
    <mj-section padding="0 25px">
        <mj-column mj-class="light-blue" width="25%" padding="5px 10px">
            <mj-text mj-class="inner">
                <strong>Price</strong>
            </mj-text>
        </mj-column>
        <mj-column mj-class="light-section" padding="5px 10px" width="75%">
            <mj-text mj-class="inner">
                {{ $eateryReview->price['label'] }}
            </mj-text>
        </mj-column>
    </mj-section>
    <mj-section padding="0 25px">
        <mj-column mj-class="light-blue" width="25%" padding="5px 10px">
            <mj-text mj-class="inner">
                <strong>Food</strong>
            </mj-text>
        </mj-column>
        <mj-column mj-class="light-section" padding="5px 10px" width="75%">
            <mj-text mj-class="inner">
                {{ ucwords($eateryReview->food_rating) }}
            </mj-text>
        </mj-column>
    </mj-section>
    <mj-section padding="0 25px">
        <mj-column mj-class="light-blue" width="25%" padding="5px 10px">
            <mj-text mj-class="inner">
                <strong>Service</strong>
            </mj-text>
        </mj-column>
        <mj-column mj-class="light-section" padding="5px 10px" width="75%">
            <mj-text mj-class="inner">
                {{ ucwords($eateryReview->service_rating) }}
            </mj-text>
        </mj-column>
    </mj-section>

    <mj-section>
        <mj-column>
            <mj-button href="{{ $eateryReview->eatery->link() }}">See more details for {{ $eateryReview->eatery->name }}</mj-button>
        </mj-column>
    </mj-section>
@endsection
