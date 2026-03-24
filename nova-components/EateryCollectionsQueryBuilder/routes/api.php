<?php

declare(strict_types=1);

use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryCuisine;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\EateryType;
use App\Models\EatingOut\EateryVenueType;
use App\Pipelines\EatingOut\GetEateries\GetEateriesFromCollectionPipeline;
use App\Services\EatingOut\Collection\Builder\BranchQueryBuilder;
use App\Services\EatingOut\Collection\Builder\EateryQueryBuilder;
use App\Services\EatingOut\Collection\Configuration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/relation', function (Request $request) {
    return match ($request->input('relation')) {
        'town_id' => EateryTown::query()
            ->orderBy('town')
            ->with(['county', 'county.country'])
            ->get()
            ->map(fn (EateryTown $town) => [
                'value' => $town->id,
                'label' => "{$town->town}, {$town->county->county}, {$town->county->country->country}",
            ]),
        'county_id' => EateryCounty::query()
            ->orderBy('county')
            ->with(['country'])
            ->get()
            ->map(fn (EateryCounty $county) => [
                'value' => $county->id,
                'label' => "{$county->county}, {$county->country->country}",
            ]),
        'country_id' => EateryCountry::query()
            ->orderBy('country')
            ->get()
            ->map(fn (EateryCountry $country) => [
                'value' => $country->id,
                'label' => $country->country,
            ]),
        'area_id' => EateryArea::query()
            ->orderBy('area')
            ->with(['town'])
            ->get()
            ->map(fn (EateryArea $area) => [
                'value' => $area->id,
                'label' => "{$area->area}, {$area->town->town}",
            ]),
        'type_id' => EateryType::query()
            ->get()
            ->map(fn (EateryType $type) => [
                'value' => $type->id,
                'label' => $type->type,
            ]),
        'venue_type_id' => EateryVenueType::query()
            ->orderBy('venue_type')
            ->get()
            ->map(fn (EateryVenueType $venueType) => [
                'value' => $venueType->id,
                'label' => $venueType->venue_type,
            ]),
        'cuisine_id' => EateryCuisine::query()
            ->orderBy('cuisine')
            ->get()
            ->map(fn (EateryCuisine $cuisine) => [
                'value' => $cuisine->id,
                'label' => $cuisine->cuisine,
            ]),
    };
});

Route::post('/has', function (Request $request) {
    return match ($request->input('relation')) {
        'features' => EateryFeature::query()
            ->orderBy('feature')
            ->get()
            ->map(fn (EateryFeature $feature) => [
                'value' => $feature->id,
                'label' => $feature->feature,
            ]),
    };
});

Route::post('/preview-query', function (Request $request) {
    $config = new Configuration(...$request->array('config'));

    $eateryQueryBuilder = new EateryQueryBuilder($config);
    $branchQueryBuilder = new BranchQueryBuilder($config);

    return [
        'eateries' => $eateryQueryBuilder->toSql(),
        'branches' => $branchQueryBuilder->toSql(),
    ];
});

Route::post('results', function (Request $request, GetEateriesFromCollectionPipeline $getEateriesFromCollectionPipeline) {
    $config = new Configuration(...$request->array('config'));

    $eateries = $getEateriesFromCollectionPipeline->run($config);

    return [
        'data' => $eateries,
    ];
});
