<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EatingOut\EateryFeature;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EatingOut\EateryFeature>
 */
class EateryFeatureFactory extends Factory
{
    protected $model = EateryFeature::class;

    public function definition()
    {
        return [
            'feature' => $this->faker->word,
            'slug' => $this->faker->slug,
        ];
    }
}
