<?php

declare(strict_types=1);

namespace App\Pipelines\Search;

use App\DataObjects\Search\SearchParameters;
use App\DataObjects\Search\SearchPipelineData;
use App\DataObjects\Search\SearchResultsCollection;
use App\Pipelines\Search\Steps\CombineResults;
use App\Pipelines\Search\Steps\HydratePage;
use App\Pipelines\Search\Steps\PaginateResults;
use App\Pipelines\Search\Steps\PrepareResource;
use App\Pipelines\Search\Steps\SearchBlogs;
use App\Pipelines\Search\Steps\SearchEateries;
use App\Pipelines\Search\Steps\SearchRecipes;
use App\Pipelines\Search\Steps\SearchShop;
use App\Pipelines\Search\Steps\SortResults;
use App\Resources\Search\SearchableItemResource;
use App\Support\State\Search\SearchState;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pipeline\Pipeline;

class PerformSearchPipeline
{
    /** @return LengthAwarePaginator<int, SearchableItemResource> */
    public function run(SearchParameters $parameters): LengthAwarePaginator
    {
        SearchState::$hasGeoSearched = false;

        $pipes = [
            SearchBlogs::class,
            SearchRecipes::class,
            SearchEateries::class,
            SearchShop::class,
            CombineResults::class,
            SortResults::class,
            PaginateResults::class,
            HydratePage::class,
            PrepareResource::class,
        ];

        $data = new SearchPipelineData($parameters, new SearchResultsCollection());

        /** @var LengthAwarePaginator<int, SearchableItemResource> $results */
        $results = app(Pipeline::class)
            ->send($data)
            ->through($pipes)
            ->thenReturn();

        return $results;
    }
}
