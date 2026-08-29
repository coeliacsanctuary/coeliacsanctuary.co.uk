<?php

declare(strict_types=1);

namespace App\Actions\Recipes;

use App\Models\Recipes\RecipeAllergen;
use App\Models\Recipes\RecipeFeature;
use App\Models\Recipes\RecipeMeal;
use Illuminate\Support\Str;

class BuildRecipeIndexTitleAction
{
    /** @param  array{features?: string[], meals?: string[], freeFrom?: string[]}  $filters */
    public function handle(array $filters = []): string
    {
        $features = array_filter($filters['features'] ?? []);
        $meals = array_filter($filters['meals'] ?? []);
        $freeFrom = array_filter($filters['freeFrom'] ?? []);

        if (count($features) + count($meals) + count($freeFrom) !== 1) {
            return 'Gluten Free Recipes';
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
            return 'Gluten Free Recipes';
        }

        return match ($slug) {
            'healthier-option' => 'Healthier Gluten Free Recipes',
            'fodmap-friendly' => 'Gluten Free Low FODMAP Recipes',
            default => "Gluten Free {$feature->feature} Recipes",
        };
    }

    protected function forMeal(string $slug): string
    {
        $meal = RecipeMeal::query()->firstWhere('slug', $slug);

        if ( ! $meal) {
            return 'Gluten Free Recipes';
        }

        return 'Gluten Free ' . Str::singular($meal->meal) . ' Recipes';
    }

    protected function forAllergen(string $slug): string
    {
        $allergen = RecipeAllergen::query()->firstWhere('slug', $slug);

        if ( ! $allergen) {
            return 'Gluten Free Recipes';
        }

        return 'Gluten Free and ' . Str::singular($allergen->allergen) . ' Free Recipes';
    }
}
