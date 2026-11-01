<?php

declare(strict_types=1);

namespace App\Builders\Recipes;

use App\Models\Recipes\RecipeAllergen;

/** @extends RecipeFilterBuilder<RecipeAllergen> */
class RecipeAllergenBuilder extends RecipeFilterBuilder
{
    public function selectableAsFilter(): static
    {
        return $this->where('slug', '!=', 'gluten');
    }
}
