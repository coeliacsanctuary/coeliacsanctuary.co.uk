<?php

declare(strict_types=1);

namespace App\Actions\Recipes;

use App\Models\Recipes\Recipe;
use App\ResourceCollections\Recipes\RecipeListCollection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\ResourceCollection;

class GetRecipesForIndexAction
{
    /**
     * @template T of ResourceCollection
     *
     * @param  array{features?: string[], meals?: string[], freeFrom?: string[]}  $filters
     * @param  class-string<T>  $resource
     * @return T
     */
    public function handle(array $filters = [], int $perPage = 12, string $resource = RecipeListCollection::class, ?string $search = null): ResourceCollection
    {
        $featureFilters = array_filter($filters['features'] ?? []);
        $mealFilters = array_filter($filters['meals'] ?? []);
        $freeFromFilters = array_filter($filters['freeFrom'] ?? []);

        return new $resource(
            Recipe::query()
                ->with(['media', 'features', 'nutrition'])
                ->when($search, fn (Builder $builder) => $builder->where(
                    fn (Builder $builder) => $builder
                        ->where('id', $search)
                        ->orWhere('title', 'LIKE', "%{$search}%")
                ))
                ->when(count($featureFilters) > 0, fn (Builder $query) => $query->hasFeatures($featureFilters))
                ->when(count($mealFilters) > 0, fn (Builder $query) => $query->hasMeals($mealFilters))
                ->when(count($freeFromFilters) > 0, fn (Builder $query) => $query->hasFreeFrom($freeFromFilters))
                ->latest()
                ->paginate($perPage)
                ->withQueryString()
        );
    }
}
