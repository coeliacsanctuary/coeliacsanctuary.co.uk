@php
    use App\Models\EatingOut\EateryRecommendation;
    use App\Models\EatingOut\Eatery;
    use App\Support\Helpers;
    use Illuminate\Support\Collection;use Illuminate\Support\Str;
    use Money\Money;
    /** @var Eatery $eatery */
    /** @var EateryRecommendation $recommendation */
    /** @var Collection<int, Eatery> $nearbyEateries */

$hideRelatedGap = true;
@endphp

@extends('mailables.mjml.layout')

@push('head')
    <mj-style>
        @media only screen and (min-width: 480px) { *[class~=prod-image] { padding-right: 10px !important;} }
    </mj-style>
@endpush

@section('header')
    <h2 style="padding: 5px 0">Your recommendation already exists!</h2>
@endsection

@section('main-content')
    <mj-section>
        <mj-column>
            <mj-text mj-class="inner">Hey {{ $recommendation->name }}</mj-text>
            @if($branch)
                @if($branch->name)
                    <mj-text mj-class="inner">
                        Thanks for your suggestion to add the <strong>{{ $branch->name }}</strong> branch of {{ $eatery->name }} in {{ $branch->town?->town }} to the Coeliac Sanctuary Eating
                        out guide, but {{ $branch->name }} is already listed in my guide.
                    </mj-text>
                    <mj-text mj-class="inner">
                        Why don't you checkout <strong>{{ $branch->name }}</strong> on Coeliac Sanctuary and leave a review to
                        let others know your experience eating out there?
                    </mj-text>
                @else
                    <mj-text mj-class="inner">
                        Thanks for your suggestion to add the <strong>{{ $branch->town?->town }}</strong> branch of <strong>{{ $eatery->name }}</strong> to the Coeliac Sanctuary Eating
                        out guide, but it is already listed in my guide.
                    </mj-text>
                    <mj-text mj-class="inner">
                        Why don't you checkout <strong>{{ $branch->name }}</strong> on Coeliac Sanctuary and leave a review to
                        let others know your experience eating out there?
                    </mj-text>
                @endif
            @else
                <mj-text mj-class="inner">
                    Thanks for your suggestion to add <strong>{{ $eatery->name }}</strong> in {{ $eatery->town?->town }} to the Coeliac Sanctuary Eating
                    out guide, but {{ $eatery->name }} is already listed in my guide.
                </mj-text>
                <mj-text mj-class="inner">
                    Why don't you checkout <strong>{{ $eatery->name }}</strong> on Coeliac Sanctuary and leave a review to
                    let others know your experience eating out there?
                </mj-text>
            @endif
            <mj-text mj-class="inner" padding-top="10px">
                Thanks, Alison - Coeliac Sanctuary
            </mj-text>
        </mj-column>
    </mj-section>

    <mj-section padding-bottom="20px">
        <mj-column>
            <mj-button href="{{ $branch ? $branch->absoluteLink() : $eatery->absoluteLink() }}">
                Visit {{ $branch && $branch->name ? $branch->name : $eatery->name }} and leave a review!
            </mj-button>
        </mj-column>
    </mj-section>

    @if($nearbyEateries->isNotEmpty())
        <mj-section mj-class="light-section" padding-top="30px">
            <mj-column>
                <mj-text mj-class="inner">
                    <h2>Have you also visited these locations in {{ $branch ? $branch->town->town : $eatery->town->town }}? Why not leave those a review too!</h2>
                </mj-text>
            </mj-column>
        </mj-section>

        <mj-section mj-class="light-section">
            @foreach($nearbyEateries as $nearbyEatery)
                <mj-column width="100%" padding-top="20px" vertical-align="middle">
                </mj-column>
                <mj-column width="80%" vertical-align="middle">
                    <mj-text mj-class="inner">
                        <a href="{{ $nearbyEatery->absoluteLink() }}">{{ $nearbyEatery->name }}</a><br/>
                        {{ $nearbyEatery->first_line_of_address }}
                    </mj-text>
                </mj-column>
                <mj-column width="20%" vertical-align="middle">
                    <mj-button mj-class="blue" align="center" href="{{ $nearbyEatery->absoluteLink() }}">
                        Review?
                    </mj-button>
                </mj-column>
            @endforeach
        </mj-section>
    @endif
@endsection
