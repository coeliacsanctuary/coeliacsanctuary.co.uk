<?php

declare(strict_types=1);

namespace App\Actions\Recipes;

use App\Builders\Recipes\RecipeBuilder;
use App\Builders\Recipes\RecipeFilterBuilder;
use App\Contracts\Recipes\FilterableRecipeRelation;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GetRecipeFiltersForIndexAction
{
    /**
     * @template TFilter of FilterableRecipeRelation
     * @param  class-string<TFilter>  $relation
     * @param  array{features?: string[], meals?: string[], freeFrom?: string[]}  $filters
     * @return Collection<int, array<string, mixed>>
     */
    public function handle(string $relation, array $filters = []): Collection
    {
        $featureFilters = array_filter($filters['features'] ?? []);
        $mealFilters = array_filter($filters['meals'] ?? []);
        $freeFromFilters = array_filter($filters['freeFrom'] ?? []);

        $column = Str::of(class_basename($relation))->after('Recipe')->lower()->toString();

        return $relation::query()
            ->selectableAsFilter()
            ->when(count($featureFilters) > 0, fn (RecipeFilterBuilder $query): RecipeFilterBuilder => $query->hasRecipesWithFeatures($featureFilters))
            ->when(count($mealFilters) > 0, fn (RecipeFilterBuilder $query): RecipeFilterBuilder => $query->hasRecipesWithMeals($mealFilters))
            ->when(count($freeFromFilters) > 0, fn (RecipeFilterBuilder $query): RecipeFilterBuilder => $query->hasRecipesWithFreeFrom($freeFromFilters))
            ->withCount([
                'recipes' => fn (RecipeBuilder $builder): RecipeBuilder => $builder
                    ->when(count($featureFilters) > 0, fn (RecipeBuilder $query): RecipeBuilder => $query->hasFeatures($featureFilters))
                    ->when(count($mealFilters) > 0, fn (RecipeBuilder $query): RecipeBuilder => $query->hasMeals($mealFilters))
                    ->when(count($freeFromFilters) > 0, fn (RecipeBuilder $query): RecipeBuilder => $query->hasFreeFrom($freeFromFilters)),
            ])
            ->orderBy($column)
            ->get()
            ->map(fn (FilterableRecipeRelation $relation) => $relation->only([$column, 'slug', 'recipes_count']));
    }
}
