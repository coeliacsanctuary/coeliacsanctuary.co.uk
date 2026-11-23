<?php

declare(strict_types=1);

namespace App\Pipelines\Search\Steps;

use Algolia\ScoutExtended\Builder;
use App\DataObjects\Search\SearchPipelineData;
use App\DataObjects\Search\SearchResultItem;
use App\Models\Blogs\Blog;
use Closure;
use Illuminate\Database\Eloquent\Collection;

class SearchBlogs
{
    public function handle(SearchPipelineData $searchPipelineData, Closure $next): mixed
    {
        if ($searchPipelineData->parameters->blogs) {
            /** @var Builder $builder */
            $builder = Blog::search($searchPipelineData->parameters->term);

            /** @var Collection<int, Blog> $results */
            $results = $builder
                ->with([
                    'getRankingInfo' => true,
                ])
                ->take(100)
                ->get();

            $searchPipelineData
                ->results
                ->blogs
                ->push(...$results->map(fn (Blog $blog): SearchResultItem => SearchResultItem::fromSearchableResult($blog))->all());
        }

        return $next($searchPipelineData);
    }
}
