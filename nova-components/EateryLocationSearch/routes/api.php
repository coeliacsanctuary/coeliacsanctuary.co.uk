<?php

declare(strict_types=1);

use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Jpeters8889\EateryLocationSearch\ResultDto;

Route::post('/search', function (Request $request): Collection {
    if ($request->input('term') === '') {
        return collect();
    }

    $countyResults = EateryCounty::withoutGlobalScopes()
        ->whereLike('county', "%{$request->input('term')}%")
        ->with(['country'])
        ->get()
        ->map(fn (EateryCounty $county) => new ResultDto(
            type: 'county',
            label: "{$county->county}, {$county->country->country}",
            matchedTerm: $county->county,
            countryId: $county->country_id,
            countyId: $county->id,
            townId: null,
        ));

    $townResults = EateryTown::withoutGlobalScopes()
        ->whereLike('town', "%{$request->input('term')}%")
        ->with(['county' => fn($query) => $query->withoutGlobalScopes(), 'county.country'])
        ->get()
        ->map(fn (EateryTown $town) => new ResultDto(
            type: $town->county->slug === 'london' ? 'borough' : 'town',
            label: "{$town->town}, {$town->county->county}, {$town->county->country->country}",
            matchedTerm: $town->town,
            countryId: $town->county->country_id,
            countyId: $town->county_id,
            townId: $town->id,
        ));

    $areaResults = EateryArea::withoutGlobalScopes()
        ->whereLike('area', "%{$request->input('term')}%")
        ->with(['town' => fn($query) => $query->withoutGlobalScopes(), 'town.county' => fn($query) => $query->withoutGlobalScopes(), 'town.county.country'])
        ->get()
        ->map(fn (EateryArea $area) => new ResultDto(
            type: 'area',
            label: "{$area->area}, {$area->town->town}, {$area->town->county->county}, {$area->town->county->country->country}",
            matchedTerm: $area->area,
            countryId: $area->town->county->country_id,
            countyId: $area->town->county_id,
            townId: $area->town_id,
            areaId: $area->id,
        ));

    /** @var Collection<int, ResultDto> $results */
    $results = collect()
        ->merge($countyResults)
        ->merge($townResults)
        ->merge($areaResults)
        ->sortByDesc([fn (ResultDto $a, ResultDto $b) => levenshtein($a->matchedTerm, $request->input('term')) <=> levenshtein($b->matchedTerm, $request->input('term'))])
        ->take(10);

    return $results;
});
