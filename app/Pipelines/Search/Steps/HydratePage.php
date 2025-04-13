<?php

declare(strict_types=1);

namespace App\Pipelines\Search\Steps;

use App\Contracts\Search\IsSearchable;
use App\DataObjects\Search\SearchResultItem;
use App\Models\Blogs\Blog;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Models\Recipes\Recipe;
use App\Models\Shop\ShopProduct;
use Closure;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class HydratePage
{
    protected array $relations = [
        Blog::class => ['media'],
        Recipe::class => ['media'],
        Eatery::class => ['country', 'county', 'town', 'restaurants'],
        NationwideBranch::class => ['eatery', 'country', 'county', 'town'],
        ShopProduct::class => ['variants', 'prices'],
    ];

    /** @param LengthAwarePaginator<int, SearchResultItem> $paginator */
    public function handle(LengthAwarePaginator $paginator, Closure $next): mixed
    {
        /** @var Collection<class-string<IsSearchable>, Collection<int, IsSearchable>> $models */
        $models = $paginator->groupBy('model')
            ->map(function (Collection $items, $model) {
                /** @var class-string<IsSearchable> $model */
                /** @var Collection<int, SearchResultItem> $items */

                return $model::query()
                    ->whereIn('id', $items->pluck('id'))
                    ->with($this->relations[$model])
                    ->get();
            });

        /** @var Collection<int, IsSearchable> $hydratedResults */
        $hydratedResults = $paginator->map(function (SearchResultItem $item) use ($models) {
            /** @var Collection<int, IsSearchable> $hydratedCollection */
            $hydratedCollection = $models->get($item->model);

            /** @var IsSearchable $res */
            $res = $hydratedCollection->where('id', $item->id)->firstOrFail();

            $res->setAttribute('_score', $item->score);
            $res->setAttribute('_resDistance', $item->distance);

            return $res;
        });

        /** @var LengthAwarePaginator<int, IsSearchable> $paginator */
        $paginator->setCollection($hydratedResults);

        return $next($paginator);
    }
}
