<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Recipes\RecipeMeal;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recipes\RecipeMeal>
 */
class RecipeMealFactory extends Factory
{
    protected $model = RecipeMeal::class;

    public function definition()
    {
        return [
            'meal' => $this->faker->word(),
            'slug' => $this->faker->slug(),
        ];
    }
}
