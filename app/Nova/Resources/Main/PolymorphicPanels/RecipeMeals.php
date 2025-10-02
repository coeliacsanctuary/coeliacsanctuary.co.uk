<?php

declare(strict_types=1);

namespace App\Nova\Resources\Main\PolymorphicPanels;

use App\Models\Recipes\Recipe;
use App\Models\Recipes\RecipeMeal;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Jpeters8889\PolymorphicPanel\PolymorphicResource;
use Laravel\Nova\Fields\Boolean;

/**
 * @codeCoverageIgnore
 */
class RecipeMeals implements PolymorphicResource
{
    public function fields(): array
    {
        return RecipeMeal::query()
            ->get()
            ->map(fn (RecipeMeal $meal) => Boolean::make(Str::title($meal->meal)))
            ->toArray();
    }

    public function relationship(): string
    {
        return 'meals';
    }

    public function check(string $key, Collection $relationship, Model $resource): bool
    {
        return $relationship
            ->filter(fn (RecipeMeal $meal): bool => $meal->meal === Str::headline($key)) /** @phpstan-ignore-line  */
            ->count() === 1;
    }

    /** @param Recipe $model */
    public function set(Collection $values, Model $model): void
    {
        /** @var callable $callback */
        $callback = fn (RecipeMeal $meal) => $model->meals()->detach($meal);

        $model->meals->each($callback);

        $values->map(fn ($value) => filter_var($value, FILTER_VALIDATE_BOOL))
            ->filter(fn ($value) => $value === true)
            ->map(fn ($value, $key) => RecipeMeal::query()->where('meal', Str::headline($key))->firstOrFail()->id)
            ->each(fn ($id) => $model->meals()->attach($id));
    }
}
