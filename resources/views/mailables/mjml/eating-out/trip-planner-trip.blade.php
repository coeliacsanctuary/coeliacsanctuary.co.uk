@php
    /** @var string $start */
    /** @var string $end */
    /** @var float $miles */
    /** @var int $hours */
    /** @var int $minutes */
    /** @var int $stops */
    /** @var string $viewUrl */
    /** @var string | null $editUrl */
@endphp

@extends('mailables.mjml.layout')

@section('header')
    <h2 style="padding: 5px 0">Your Trip</h2>
@endsection

@section('main-content')
    <mj-section>
        <mj-column>
            <mj-text mj-class="inner">Hey there</mj-text>
            <mj-text mj-class="inner">
                Here's the link to your trip from <strong>{{ $start }}</strong> to <strong>{{ $end }}</strong>, with
                all of the gluten free places to eat along the way.
            </mj-text>
            <mj-text mj-class="inner" padding-top="10px"> Thanks, Alison - Coeliac Sanctuary </mj-text>
        </mj-column>
    </mj-section>

    <mj-section padding="0 25px">
        <mj-column mj-class="light-blue" width="25%" padding="5px 10px">
            <mj-text mj-class="inner">
                <strong>Distance</strong>
            </mj-text>
        </mj-column>
        <mj-column mj-class="light-section" padding="5px 10px" width="75%">
            <mj-text mj-class="inner"> {{ $miles }} miles </mj-text>
        </mj-column>
    </mj-section>

    <mj-section padding="0 25px">
        <mj-column mj-class="light-blue" width="25%" padding="5px 10px">
            <mj-text mj-class="inner">
                <strong>Driving time</strong>
            </mj-text>
        </mj-column>
        <mj-column mj-class="light-section" padding="5px 10px" width="75%">
            <mj-text mj-class="inner"> {{ $hours > 0 ? "{$hours}h " : '' }}{{ $minutes }}m </mj-text>
        </mj-column>
    </mj-section>

    @if ($stops > 0)
        <mj-section padding="0 25px">
            <mj-column mj-class="light-blue" width="25%" padding="5px 10px">
                <mj-text mj-class="inner">
                    <strong>Stops</strong>
                </mj-text>
            </mj-column>
            <mj-column mj-class="light-section" padding="5px 10px" width="75%">
                <mj-text mj-class="inner"> {{ $stops }} suggested {{ $stops === 1 ? 'stop' : 'stops' }} </mj-text>
            </mj-column>
        </mj-section>
    @endif

    <mj-section>
        <mj-column>
            <mj-button href="{{ $viewUrl }}">View your trip</mj-button>
            <mj-text mj-class="inner" align="center">You can share this link with anyone you're travelling with.</mj-text>
        </mj-column>
    </mj-section>

    @if ($editUrl)
        <mj-section>
            <mj-column>
                <mj-button href="{{ $editUrl }}">Edit your trip</mj-button>
                <mj-text mj-class="inner" align="center">
                    This link lets you change your trip from any device, so keep it to yourself.
                </mj-text>
            </mj-column>
        </mj-section>
    @endif
@endsection
