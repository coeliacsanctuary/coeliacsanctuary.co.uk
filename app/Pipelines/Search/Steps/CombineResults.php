<?php

declare(strict_types=1);

namespace App\Pipelines\Search\Steps;

use App\DataObjects\Search\SearchPipelineData;
use Closure;

class CombineResults
{
    public function handle(SearchPipelineData $searchPipelineData, Closure $next): mixed
    {
        $combinedData = collect([
            ...$searchPipelineData->results->blogs->all(),
            ...$searchPipelineData->results->recipes->all(),
            ...$searchPipelineData->results->eateries->all(),
            ...$searchPipelineData->results->shop->all(),
        ]);

        return $next($combinedData);
    }
}
