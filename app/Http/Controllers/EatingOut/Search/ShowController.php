<?php

declare(strict_types=1);

namespace App\Http\Controllers\EatingOut\Search;

use App\Actions\OpenGraphImages\GetOpenGraphImageForRouteAction;
use App\Http\Response\Inertia;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EaterySearchTerm;
use App\Pipelines\EatingOut\GetEateries\GetSearchResultsPipeline;
use App\Resources\EatingOut\EateryListResource;
use App\Services\EatingOut\Filters\GetFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Inertia\Response;

class ShowController
{
    public function __invoke(
        Request $request,
        EaterySearchTerm $eaterySearchTerm,
        Inertia $inertia,
        GetSearchResultsPipeline $getSearchResultsPipeline,
        GetFilters $getFiltersForEateriesAction,
        GetOpenGraphImageForRouteAction $getOpenGraphImageForRouteAction,
    ): Response {
        /** @var array{categories: string[] | null, features: string[] | null, venueTypes: string [] | null, county: string | int | null } $filters */
        $filters = [
            'categories' => $request->has('filter.category') ? explode(',', $request->string('filter.category')->toString()) : null,
            'venueTypes' => $request->has('filter.venueType') ? explode(',', $request->string('filter.venueType')->toString()) : null,
            'features' => $request->has('filter.feature') ? explode(',', $request->string('filter.feature')->toString()) : null,
        ];

        $eateries = $getSearchResultsPipeline->run($eaterySearchTerm, $filters);

        /** @var EateryListResource | null $jsonResource */
        $jsonResource = $eateries->collect()->first();

        /** @var Eatery|null $firstResult */
        $firstResult = $jsonResource?->resource?->load(['town', 'county', 'country']);

        $image = match (true) {
            $firstResult?->town?->image => $firstResult->town->image,
            $firstResult?->county?->image => $firstResult->county->image,
            $firstResult?->country?->image => $firstResult->country->image,
            default => EateryCountry::query()->where('country', 'England')->firstOrFail()->image,
        };

        return $inertia
            ->title("{$eaterySearchTerm->term} - Search Results")
            ->metaImage($getOpenGraphImageForRouteAction->handle('eatery'))
            ->doNotTrack()
            ->render('EatingOut/SearchResults', [
                'term' => fn () => $eaterySearchTerm->term,
                'range' => fn () => $eaterySearchTerm->range,
                'image' => fn () => $image,
                'eateries' => fn () => $eateries,
                //                'filters' => fn () => $getFiltersForEateriesAction->handle(fn (Builder $query) => $query->whereIn('id', Arr::pluck($eateries->all(), 'id')), $filters),
                'filters' => fn () => $getFiltersForEateriesAction->handle($filters),
                'latlng' => fn () => $firstResult ? ['lat' => $firstResult->lat, 'lng' => $firstResult->lng] : null,
            ]);
    }
}
