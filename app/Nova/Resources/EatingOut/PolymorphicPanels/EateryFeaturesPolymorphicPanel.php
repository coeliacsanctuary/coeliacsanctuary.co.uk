<?php

declare(strict_types=1);

namespace App\Nova\Resources\EatingOut\PolymorphicPanels;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryFeature;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Jpeters8889\PolymorphicPanel\PolymorphicResource;
use Laravel\Nova\Fields\Boolean;

/**
 * @codeCoverageIgnore
 */
class EateryFeaturesPolymorphicPanel implements PolymorphicResource
{
    public function fields(): array
    {
        return EateryFeature::query()
            ->get()
            ->transform(fn (EateryFeature $feature) => Boolean::make(Str::title($feature->feature)))
            ->toArray();
    }

    public function relationship(): string
    {
        return 'features';
    }

    /** @phpstan-param  Collection<int, EateryFeature>  $relationship */
    public function check($key, Collection $relationship, Model $resource): bool
    {
        return $relationship->filter(function (EateryFeature $feature) use ($key) {
            $feature = Str::of($feature->feature)
                ->lower()
                ->toString();

            $key = Str::of($key)
                ->headline()
                ->lower()
                ->toString();

            return $feature === $key;
        })->count() === 1;
    }

    /**
     * @phpstan-param  Collection<string, int>  $values
     * @phpstan-param  Eatery  $model
     */
    public function set(Collection $values, Model $model): void
    {
        $model->features->each(fn (EateryFeature $feature) => $model->features()->detach($feature));

        $values->map(fn ($value) => filter_var($value, FILTER_VALIDATE_BOOL))
            ->filter(fn ($value) => $value === true)
            ->map(fn ($value, $key) => EateryFeature::query()->where('feature', Str::headline($key))->firstOrFail()->id)
            ->each(fn ($id) => $model->features()->attach($id));
    }
}
