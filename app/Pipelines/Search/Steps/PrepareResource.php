<?php

declare(strict_types=1);

namespace App\Pipelines\Search\Steps;

use App\Contracts\Search\IsSearchable;
use App\Resources\Search\SearchableItemResource;
use Closure;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PrepareResource
{
    /**
     * @param  LengthAwarePaginator<IsSearchable>  $paginator
     * @return LengthAwarePaginator<SearchableItemResource>
     */
    public function handle(LengthAwarePaginator $paginator, Closure $next): mixed
    {
        /** @var Collection<int, SearchableItemResource> $items */
        $items = $paginator->map(fn (IsSearchable $searchable) => SearchableItemResource::make($searchable));

        /** @var LengthAwarePaginator<SearchableItemResource> $paginator */
        $paginator->setCollection($items);

        return $next($paginator);
    }
}
