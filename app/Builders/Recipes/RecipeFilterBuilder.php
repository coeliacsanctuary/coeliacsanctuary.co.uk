<?php

declare(strict_types=1);

namespace App\Builders\Recipes;

use App\Contracts\Recipes\FilterableRecipeRelation;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model&FilterableRecipeRelation
 *
 * @extends Builder<TModel>
 */
class RecipeFilterBuilder extends Builder
{
    public function selectableAsFilter(): static
    {
        return $this;
    }

    public function hasRecipesWithFeatures(array $features): static
    {
        return $this->whereHas('recipes', fn (RecipeBuilder $builder) => $builder->hasFeatures($features));
    }

    public function hasRecipesWithMeals(array $meals): static
    {
        return $this->whereHas('recipes', fn (RecipeBuilder $builder) => $builder->hasMeals($meals));
    }

    public function hasRecipesWithFreeFrom(array $freeFrom): static
    {
        return $this->whereHas('recipes', fn (RecipeBuilder $builder) => $builder->hasFreeFrom($freeFrom));
    }
}
