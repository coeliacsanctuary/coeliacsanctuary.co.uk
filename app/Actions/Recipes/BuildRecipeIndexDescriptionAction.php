<?php

declare(strict_types=1);

namespace App\Actions\Recipes;

use App\Models\Recipes\RecipeAllergen;
use App\Models\Recipes\RecipeFeature;
use App\Models\Recipes\RecipeMeal;
use Illuminate\Support\Str;

class BuildRecipeIndexDescriptionAction
{
    /** @param  array{features?: string[], meals?: string[], freeFrom?: string[]}  $filters */
    public function handle(array $filters = []): string
    {
        $features = array_filter($filters['features'] ?? []);
        $meals = array_filter($filters['meals'] ?? []);
        $freeFrom = array_filter($filters['freeFrom'] ?? []);

        if (count($features) + count($meals) + count($freeFrom) !== 1) {
            return $this->unfiltered();
        }

        if ($features !== []) {
            return $this->forFeature((string) reset($features));
        }

        if ($meals !== []) {
            return $this->forMeal((string) reset($meals));
        }

        return $this->forAllergen((string) reset($freeFrom));
    }

    protected function forFeature(string $slug): string
    {
        $feature = RecipeFeature::query()->firstWhere('slug', $slug);

        if ( ! $feature) {
            return $this->unfiltered();
        }

        $opening = match ($slug) {
            'healthier-option' => 'Healthier gluten free recipes',
            'fodmap-friendly' => 'Gluten free low FODMAP recipes',
            default => 'Gluten free ' . Str::lower($feature->feature) . ' recipes',
        };

        return $this->build($opening, 'coeliac friendly bakes, dinners, breakfasts and puddings');
    }

    protected function forMeal(string $slug): string
    {
        $meal = RecipeMeal::query()->firstWhere('slug', $slug);

        if ( ! $meal) {
            return $this->unfiltered();
        }

        $name = Str::lower(Str::singular($meal->meal));

        return $this->build("Gluten free {$name} recipes", "coeliac friendly {$name} ideas");
    }

    protected function forAllergen(string $slug): string
    {
        $allergen = RecipeAllergen::query()->firstWhere('slug', $slug);

        if ( ! $allergen) {
            return $this->unfiltered();
        }

        $name = Str::lower(Str::singular($allergen->allergen));

        return $this->build("Gluten free and {$name} free recipes", 'bakes, dinners and puddings');
    }

    protected function unfiltered(): string
    {
        return $this->build('Gluten free recipes', 'coeliac friendly bakes, dinners, breakfasts and puddings');
    }

    protected function build(string $opening, string $examples): string
    {
        return "{$opening} from Coeliac Sanctuary — tried and tested {$examples}, all using simple supermarket ingredients.";
    }
}
