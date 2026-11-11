<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Recipes\RecipeAllergen;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recipes\RecipeAllergen>
 */
class RecipeAllergenFactory extends Factory
{
    protected $model = RecipeAllergen::class;

    public function definition()
    {
        return [
            'allergen' => $this->faker->word(),
            'slug' => $this->faker->slug(),
        ];
    }
}
