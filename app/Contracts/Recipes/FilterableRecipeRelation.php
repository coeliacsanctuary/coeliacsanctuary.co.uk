<?php

declare(strict_types=1);

namespace App\Contracts\Recipes;

use App\Builders\Recipes\RecipeFilterBuilder;
use App\Models\Recipes\Recipe;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @template TModel of Model&FilterableRecipeRelation
 * @mixin Model
 */
interface FilterableRecipeRelation
{
    /** @return RecipeFilterBuilder<TModel>  */
    public static function query();

    /** @return BelongsToMany<Recipe, TModel> */
    public function recipes(): BelongsToMany;
}
