<?php

declare(strict_types=1);

namespace App\Pipelines\Search\Steps;

use Algolia\ScoutExtended\Builder;
use App\DataObjects\Search\SearchPipelineData;
use App\DataObjects\Search\SearchResultItem;
use App\Models\Shop\ShopProduct;
use Closure;
use Illuminate\Database\Eloquent\Collection;

class SearchShop
{
    public function handle(SearchPipelineData $searchPipelineData, Closure $next): mixed
    {
        if ($searchPipelineData->parameters->shop) {
            /** @var Builder $builder */
            $builder = ShopProduct::search($searchPipelineData->parameters->term);

            /** @var Collection<int, ShopProduct> $results */
            $results = $builder
                ->with([
                    'getRankingInfo' => true,
                ])
                ->take(100)
                ->get();

            $searchPipelineData
                ->results
                ->shop
                ->push(...$results->map(fn (ShopProduct $product): SearchResultItem => SearchResultItem::fromSearchableResult($product))->all());
        }

        return $next($searchPipelineData);
    }
}
