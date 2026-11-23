<?php

declare(strict_types=1);

namespace App\Pipelines\Search\Steps;

use App\DataObjects\Search\SearchResultItem;
use Closure;
use Illuminate\Support\Collection;

class SortResults
{
    /**
     * @param  Collection<int, SearchResultItem>  $results
     */
    public function handle(Collection $results, Closure $next): mixed
    {
        $results = $results->sortBy([
            fn (SearchResultItem $a, SearchResultItem $b): int => ($a->distance ?? 9999) <=> ($b->distance ?? 9999),
            ['firstWordPosition', 'asc'],
            ['score', 'desc'],
        ]);

        return $next($results->values());
    }
}
