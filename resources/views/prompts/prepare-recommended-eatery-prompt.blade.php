@php
    use App\Models\EatingOut\EateryRecommendation;

    /** @var EateryRecommendation $recommendation */
@endphp

The eatery recommendation is:

Place Name: {{ $recommendation->place_name }}
Place Address: {{ $recommendation->place_location }}
Place Website: {{ $recommendation->place_web_address }}
Place Venue Type: {{ $recommendation->venueType?->venue_type }}
Place Info: {{ $recommendation->place_details }}
