@php
    use App\Models\EatingOut\Eatery;
    use App\Models\EatingOut\EateryTown;
    use Illuminate\Support\Collection;
    use Illuminate\Support\Str;
    /** @var Collection<int, Eatery> $eateries */
    /** @var EateryTown $town */
@endphp

@props(['eateries', 'town'])

@if($eateries->isNotEmpty())
    <!-- BEGIN: NEARBY EATERIES -->
    <mj-section mj-class="light-section" padding="20px 15px 5px">
        <mj-column>
            <mj-text align="center">
                <h2>More gluten free places in {{ $town->town }}</h2>
            </mj-text>
            <mj-text align="center" font-size="14px" line-height="1.4" padding-top="8px">
                Been to any of these? Leaving a quick review helps other coeliacs eat out safely.
            </mj-text>
        </mj-column>
    </mj-section>

    @foreach($eateries as $nearbyEatery)
        @php
            $meta = array_filter([
                $nearbyEatery->venueType?->venue_type,
                $nearbyEatery->rating_count
                    ? '★ ' . number_format((float) $nearbyEatery->rating, 1) . ' (' . $nearbyEatery->rating_count . ' ' . Str::plural('review', (int) $nearbyEatery->rating_count) . ')'
                    : null,
            ]);
        @endphp

        <mj-section mj-class="light-section" padding="5px 15px">
            <mj-column background-color="#ffffff" border-radius="6px" padding="14px">
                <mj-text font-size="17px" line-height="1.3">
                    <a href="{{ $nearbyEatery->absoluteLink() }}">{{ $nearbyEatery->name }}</a>
                </mj-text>

                @if($meta !== [])
                    <mj-text color="#777" font-size="13px" padding-top="4px">
                        {{ implode(' • ', $meta) }}
                    </mj-text>
                @endif

                <mj-text font-size="14px" line-height="1.4" padding-top="6px">
                    {{ $nearbyEatery->first_line_of_address }}
                </mj-text>

                <mj-text font-size="13px" padding-top="10px">
                    <a href="{{ $nearbyEatery->absoluteLink() }}" style="color:#29719f">Leave a review →</a>
                </mj-text>
            </mj-column>
        </mj-section>
    @endforeach

    <mj-section mj-class="light-section" padding="15px 15px 20px">
        <mj-column>
            <mj-button href="{{ $town->absoluteLink() }}">
                See all places in {{ $town->town }}
            </mj-button>
        </mj-column>
    </mj-section>
    <!-- END: NEARBY EATERIES -->
@endif
