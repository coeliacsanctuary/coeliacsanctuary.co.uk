<?php

declare(strict_types=1);

namespace App\Nova\Resources\Main\PolymorphicPanels;

use App\Models\Shop\ShopCategory;
use App\Models\Shop\ShopMassDiscount;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Jpeters8889\PolymorphicPanel\PolymorphicResource;
use Laravel\Nova\Fields\Boolean;

/**
 * @codeCoverageIgnore
 */
class ShopCategories implements PolymorphicResource
{
    public function fields(): array
    {
        return ShopCategory::query()
            ->get()
            ->map(fn (ShopCategory $category) => Boolean::make(Str::title($category->title)))
            ->toArray();
    }

    public function relationship(): string
    {
        return 'assignedCategories';
    }

    public function check($key, Collection $relationship, Model $resource): bool
    {
        return $relationship
            ->filter(fn (ShopCategory $category): bool => $category->title === Str::headline($key)) /** @phpstan-ignore-line  */
            ->count() === 1;
    }

    /** @param ShopMassDiscount $model */
    public function set(Collection $values, Model $model): void
    {
        /** @var callable $callback */
        $callback = fn (ShopCategory $category) => $model->assignedCategories()->detach($category);

        $model->assignedCategories()->each($callback);

        $values->map(fn ($value) => filter_var($value, FILTER_VALIDATE_BOOL))
            ->filter(fn ($value) => $value === true)
            ->map(fn ($value, $key) => ShopCategory::query()->where('title', Str::headline($key))->firstOrFail()->id)
            ->each(fn ($id) => $model->allergens()->attach($id));
    }
}
