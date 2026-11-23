<?php

declare(strict_types=1);

namespace App\Pipelines\Search\Steps;

use Algolia\ScoutExtended\Builder;
use App\DataObjects\Search\SearchPipelineData;
use App\DataObjects\Search\SearchResultItem;
use App\Models\Recipes\Recipe;
use Closure;
use Illuminate\Database\Eloquent\Collection;

class SearchRecipes
{
    public function handle(SearchPipelineData $searchPipelineData, Closure $next): mixed
    {
        if ($searchPipelineData->parameters->recipes) {
            /** @var Builder $builder */
            $builder = Recipe::search($searchPipelineData->parameters->term);

            /** @var Collection<int, Recipe> $results */
            $results = $builder
                ->with([
                    'getRankingInfo' => true,
                ])
                ->take(100)
                ->get();

            $searchPipelineData
                ->results
                ->recipes
                ->push(...$results->map(fn (Recipe $recipe): SearchResultItem => SearchResultItem::fromSearchableResult($recipe))->all());
        }

        return $next($searchPipelineData);
    }
}
