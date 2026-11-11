<?php

declare(strict_types=1);

namespace App\Models\Recipes;

use App\Builders\Recipes\RecipeAllergenBuilder;
use App\Contracts\Recipes\FilterableRecipeRelation;
use Illuminate\Database\Eloquent\Attributes\UseEloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/** @implements FilterableRecipeRelation<$this> */
#[UseEloquentBuilder(RecipeAllergenBuilder::class)]
class RecipeAllergen extends Model implements FilterableRecipeRelation
{
    /** @return BelongsToMany<Recipe, $this> */
    public function recipes(): BelongsToMany
    {
        return $this->belongsToMany(Recipe::class, 'recipe_assigned_allergens', 'allergen_type_id', 'recipe_id');
    }
}
