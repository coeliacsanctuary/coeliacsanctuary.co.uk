<?php

declare(strict_types=1);

namespace App\Builders\Recipes;

use App\Models\Recipes\Recipe;
use App\Models\Recipes\RecipeAllergen;
use App\Models\Recipes\RecipeFeature;
use App\Models\Recipes\RecipeMeal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/** @extends Builder<Recipe> */
class RecipeBuilder extends Builder
{
    public function hasFeatures(array $features): static
    {
        $ids = Cache::remember(
            'recipe-feature-ids-' . implode('-', $features),
            now()->addSecond(),
            fn () => RecipeFeature::query()->whereIn('slug', $features)->pluck('id'),
        );

        foreach ($ids as $id) {
            /** @phpstan-ignore argument.type */
            $this->whereRaw(DB::raw("exists (select * from recipe_assigned_features f where f.recipe_id = recipes.id and f.feature_type_id = {$id})"));
        }

        return $this;
    }

    public function hasMeals(array $meals): static
    {
        $ids = Cache::remember(
            'recipe-meal-ids-' . implode('-', $meals),
            now()->addSecond(),
            fn () => RecipeMeal::query()->whereIn('slug', $meals)->pluck('id'),
        );

        foreach ($ids as $id) {
            /** @phpstan-ignore argument.type */
            $this->whereRaw(DB::raw("exists (select * from recipe_assigned_meals m where m.recipe_id = recipes.id and m.meal_type_id = {$id})"));
        }

        return $this;
    }

    public function hasFreeFrom(array $freeFrom): static
    {
        $ids = Cache::remember(
            'recipe-freefrom-ids-' . implode('-', $freeFrom),
            now()->addSecond(),
            fn () => RecipeAllergen::query()->whereIn('slug', $freeFrom)->pluck('id'),
        );

        foreach ($ids as $id) {
            /** @phpstan-ignore argument.type */
            $this->whereRaw(DB::raw("exists (select * from recipe_assigned_allergens a where a.recipe_id = recipes.id and a.allergen_type_id = {$id})"));
        }

        return $this;
    }
}
