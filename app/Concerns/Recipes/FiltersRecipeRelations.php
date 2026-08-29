<?php

declare(strict_types=1);

namespace App\Concerns\Recipes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @extends Model<FiltersRecipeRelations>
 */
trait FiltersRecipeRelations
{
    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeSelectableAsFilter(Builder $query): Builder
    {
        return $query;
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeHasRecipesWithFeatures(Builder $query, array $features): Builder
    {
        /** @phpstan-ignore-next-line */
        return $query->whereHas('recipes', fn (Builder $builder) => $builder->hasFeatures($features));
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeHasRecipesWithMeals(Builder $query, array $meals): Builder
    {
        /** @phpstan-ignore-next-line */
        return $query->whereHas('recipes', fn (Builder $builder) => $builder->hasMeals($meals));
    }

    /**
     * @param  Builder<$this>  $query
     * @return Builder<$this>
     */
    public function scopeHasRecipesWithFreeFrom(Builder $query, array $freeFrom): Builder
    {
        /** @phpstan-ignore-next-line */
        return $query->whereHas('recipes', fn (Builder $builder) => $builder->hasFreeFrom($freeFrom));
    }
}
