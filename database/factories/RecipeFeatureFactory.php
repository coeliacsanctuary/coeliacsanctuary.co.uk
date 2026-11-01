<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Recipes\RecipeFeature;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Recipes\RecipeFeature>
 */
class RecipeFeatureFactory extends Factory
{
    protected $model = RecipeFeature::class;

    public function definition()
    {
        return [
            'feature' => $this->faker->word,
            'slug' => $this->faker->slug,
        ];
    }
}
