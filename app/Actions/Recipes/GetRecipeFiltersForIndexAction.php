<?php

declare(strict_types=1);

namespace App\Actions\Recipes;

use App\Contracts\Recipes\FilterableRecipeRelation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GetRecipeFiltersForIndexAction
{
    /**
     * @template TFilter of FilterableRecipeRelation
     *
     * @param  class-string<TFilter>  $relation
     * @param  array{features?: string[], meals?: string[], freeFrom?: string[]}  $filters
     * @return Collection<int, array>
     */
    public function handle(string $relation, array $filters = []): Collection
    {
        $featureFilters = array_filter($filters['features'] ?? []);
        $mealFilters = array_filter($filters['meals'] ?? []);
        $freeFromFilters = array_filter($filters['freeFrom'] ?? []);

        $column = Str::of(class_basename($relation))->after('Recipe')->lower()->toString();

        /** @var Builder<TFilter> $query */
        $query = $relation::query();  /** @phpstan-ignore-line */

        return $query
            ->when(count($featureFilters) > 0, fn (Builder $query) => $query->hasRecipesWithFeatures($featureFilters))
            ->when(count($mealFilters) > 0, fn (Builder $query) => $query->hasRecipesWithMeals($mealFilters))
            ->when(count($freeFromFilters) > 0, fn (Builder $query) => $query->hasRecipesWithFreeFrom($freeFromFilters))
            ->withCount([
                'recipes' => fn (Builder $builder): Builder => $builder
                    ->when(count($featureFilters) > 0, fn (Builder $query): Builder => $query->hasFeatures($featureFilters))/** @phpstan-ignore-line */
                    ->when(count($mealFilters) > 0, fn (Builder $query): Builder => $query->hasMeals($mealFilters))/** @phpstan-ignore-line */
                    ->when(count($freeFromFilters) > 0, fn (Builder $query): Builder => $query->hasFreeFrom($freeFromFilters)),/** @phpstan-ignore-line */
            ])
            ->orderBy($column)
            ->get()
            ->map(fn (FilterableRecipeRelation $relation) => $relation->only([$column, 'slug', 'recipes_count']));
    }
}
