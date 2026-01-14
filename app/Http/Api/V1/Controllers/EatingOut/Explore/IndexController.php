<?php

declare(strict_types=1);

namespace App\Http\Api\V1\Controllers\EatingOut\Explore;

use App\Actions\EatingOut\CreateSearchAction;
use App\Http\Api\V1\Requests\EatingOut\ExploreRequest;
use App\Http\Api\V1\Resources\EatingOut\ExploreEateryResource;
use App\Pipelines\EatingOut\GetEateries\GetSearchResultsPipeline;
use App\Services\EatingOut\Filters\GetFiltersForSearchResults;

class IndexController
{
    public function __invoke(
        ExploreRequest $request,
        CreateSearchAction $createSearchAction,
        GetSearchResultsPipeline $getSearchResultsPipeline,
        GetFiltersForSearchResults $getFiltersForSearchResults,
    ): array {
        $searchTerm = $createSearchAction->handle($request->string('search')->toString(), $request->integer('range', 10));

        /** @var array{categories: array<string>|null, features: array<string>|null, venueTypes: array<string>|null, county: int|string|null} $filters */
        $filters = [
            'categories' => $request->filled('filter.category') ? explode(',', $request->string('filter.category')->toString()) : null,
            'venueTypes' => $request->filled('filter.venueType') ? explode(',', $request->string('filter.venueType')->toString()) : null,
            'features' => $request->filled('filter.feature') ? explode(',', $request->string('filter.feature')->toString()) : null,
        ];

        return [
            'data' => [
                'eateries' => $getSearchResultsPipeline->run($searchTerm, $filters, 'distance', ExploreEateryResource::class),
                'filters' => $getFiltersForSearchResults->usingSearchKey($searchTerm->key)->handle($filters),
            ],
        ];
    }
}
