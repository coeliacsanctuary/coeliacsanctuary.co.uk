@php
    use App\Models\EatingOut\Eatery;

    /** @var \Illuminate\Database\Eloquent\Collection<int, Eatery> $chains */
@endphp
Number of Chains: {{ number_format($chains->count()) }}
Number of Chains with Branch Listings: {{ number_format($chainsWithBranches->count()) }}
Total Branches Listed: {{ number_format($branches) }}
Venue Types: {{ $venueTypes }}
Cuisines: {{ $cuisines }}

## Chains
@foreach ($chains as $chain)

### {!! $chain->name !!}
@if ($chain->venueType)
Venue Type: {{ $chain->venueType->venue_type }}
@endif
@if ($chain->cuisine)
Cuisine: {{ $chain->cuisine->cuisine }}
@endif
@if ($chain->nationwide_branches_count > 0)
Branches Listed: {{ number_format($chain->nationwide_branches_count) }}
@endif
@if ($chain->reviews_count >= $minimumReviewsForRating)
Rating: {{ number_format((float) $chain->reviews_avg_rating, 1) }} from {{ $chain->reviews_count }} reviews
@endif
Link: {!! $chain->absoluteLink() !!}
@endforeach
