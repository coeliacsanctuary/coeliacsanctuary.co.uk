<?php

declare(strict_types=1);

namespace App\Models\Recipes;

use App\Concerns\Recipes\FiltersRecipeRelations;
use App\Contracts\Recipes\FilterableRecipeRelation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/** @implements FilterableRecipeRelation<$this> */
class RecipeMeal extends Model implements FilterableRecipeRelation
{
    use FiltersRecipeRelations;

    /** @return BelongsToMany<Recipe, $this> */
    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'recipe_assigned_meals', 'meal_type_id', 'recipe_id');
    }
}
