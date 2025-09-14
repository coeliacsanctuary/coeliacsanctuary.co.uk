<?php

declare(strict_types=1);

namespace App\Nova\Resources\Main\PolymorphicPanels;

use App\Models\Recipes\Recipe;
use App\Models\Recipes\RecipeAllergen;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Jpeters8889\PolymorphicPanel\PolymorphicResource;
use Laravel\Nova\Fields\Boolean;

/**
 * @codeCoverageIgnore
 */
class RecipeAllergens implements PolymorphicResource
{
    public function fields(): array
    {
        return RecipeAllergen::query()
            ->get()
            ->map(fn (RecipeAllergen $allergen) => Boolean::make(Str::title($allergen->allergen)))
            ->toArray();
    }

    public function relationship(): string
    {
        return 'allergens';
    }

    /** @param Recipe $resource */
    public function check($key, Collection $relationship, Model $resource): bool
    {
        if(!$resource->exists) {
            return false;
        }

        return $relationship
            ->filter(fn (RecipeAllergen $allergen): bool => $allergen->allergen === Str::headline($key)) /** @phpstan-ignore-line  */
            ->count() === 0;
    }

    /** @param Recipe $model */
    public function set(Collection $values, Model $model): void
    {
        /** @var callable $callback */
        $callback = fn (RecipeAllergen $allergen) => $model->allergens()->detach($allergen);

        $model->allergens->each($callback);

        $values->map(fn ($value) => filter_var($value, FILTER_VALIDATE_BOOL))
            ->filter(fn ($value) => $value === false)
            ->map(fn ($value, $key) => RecipeAllergen::query()->where('allergen', Str::headline($key))->firstOrFail()->id)
            ->each(fn ($id) => $model->allergens()->attach($id));
    }
}
