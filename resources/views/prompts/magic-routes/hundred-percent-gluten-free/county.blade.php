@php
    use App\Models\EatingOut\Eatery;
    use App\Models\EatingOut\EateryCounty;
    use App\Models\EatingOut\EateryTown;
    use Illuminate\Support\Collection;

    /** @var Collection<int, Eatery> $eateries  */
    /** @var Collection<string, Collection<int, Eatery>> $townGroups */
    /** @var EateryCounty $county */

    $totalEateries = $eateries->count();
    $totalTowns = $townGroups->count();
    $averageRatingInCounty = $eateries->average(fn(Eatery $eatery) => (float)$eatery->average_rating);
    $totalRatingsInCounty = $eateries->sum(fn(Eatery $eatery) => $eatery->reviews->count());

    $allEateriesInCounty = $county->activeTowns->sum(fn(EateryTown $town) => $town->live_eateries_count + $town->live_branches_count)
@endphp

You are an assistant on the Coeliac Sanctuary website, a key feature of the website is an extensive eating out guide of places in the UK and Ireland where people with Coeliac disease can safely eat out.

Your task is to generate SEO rich content for a dedicated page on 100% gluten free places to eat out in the county of {{ $county->county }} using the below data and instructions.

Feel free to use standard markdown/html markdown for emphasis.

Please generate a SEO rich page introduction, at least 4 paragraphs, please include in a natural way:
    - Generic content about the county, what its known for, its tourist hot spots, with a specific focus on the towns included below
    - The number of 100% gluten free eateries in the county
    - The average rating of those eateries, but only if the average is worth mentioning
    - The total number of eateries in that county overall in our eating out guide.
    - A link or multiple links to the main {{ $county->county }} within the eating out guide, but naturally, and where it makes sense.
    - Mix the structure, and wording up each time, dont start each one in a standard, consistent way, be unique, SEO rich, and natural.

Focus the content on the gluten free aspects, rather than generic aspects, but dont repeat yourself.

## County Facts
Number of 100% Gluten Free Eateries in {{ $county->county }}: {{ $totalEateries }}
Number of eateries in {{ $county->county }}: {{ $allEateriesInCounty }}
Average Rating of 100% GF Eateries in {{ $county->county }}: {{ $averageRatingInCounty }}/5
Number of ratings: {{ $totalRatingsInCounty }}
Absolute Link: {{ $county->absoluteLink() }}

Please place your SEO rich page introduction in the `page_intro` key of the structured output data.

## Town Breakdown

For each town below, please generate an SEO rich introduction, one paragraph, not too long, please include in a natural way:
    - Generic content about the town, what its known for, just a short sentence
    - The details on the eateries listed below, focusing on the most popular, but mention all of them, no need to go in to much detail as they will have their own section below the intro.
    - Mix the structure, and wording up each time, dont start each one in a standard, consistent way, be unique, SEO rich, and natural.
    - There is no need to link to the county, or town page in this intro.

Focus the content on the gluten free aspects, rather than generic aspects, but dont repeat yourself.

@foreach($townGroups as $town => $townEateries)
    ### {{ $townEateries->first()->town->town }}

    @foreach($townEateries as $eatery)
        ** {{ $eatery->name }} **
        {{ $eatery->info }}

        Average Rating: {{ $eatery->average_rating }}/5
        Number of ratings: {{ $eatery->reviews->count() }}
    @endforeach

    Place this this SEO rich town overview in the `{{ $eatery->town->slug }}` key in the structured output data.
@endforeach

## Additional guidelines

- Don't refer to the website, or the guide or anything as 'our' - refer to it in first person, ie 'my guide'
- When mentioning ratings, format them as 'x out of 5 stars', and round to the nearest half, if it is a whole number, then omit the .0, eg '4 out of 5 stars' or '3.5 out of 5 stars'
