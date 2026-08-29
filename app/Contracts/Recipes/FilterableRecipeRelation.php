<?php

declare(strict_types=1);

namespace App\Contracts\Recipes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @template TModel of Model
 * @mixin Model
 */
interface FilterableRecipeRelation
{
    /** @return Builder<TModel>  */
    public static function query();

    /**
     * @param Builder<TModel> $query
     * @return Builder<TModel>
     */
    public function scopeSelectableAsFilter(Builder $query): Builder;

    /**
     * @param Builder<TModel> $query
     * @return Builder<TModel>
     */
    public function scopeHasRecipesWithFeatures(Builder $query, array $features): Builder;

    /**
     * @param Builder<TModel> $query
     * @return Builder<TModel>
     */
    public function scopeHasRecipesWithMeals(Builder $query, array $meals): Builder;

    /**
     * @param Builder<TModel> $query
     * @return Builder<TModel>
     */
    public function scopeHasRecipesWithFreeFrom(Builder $query, array $freeFrom): Builder;
}
