<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EatingOut\EateryCuisine;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EatingOut\EateryCuisine>
 */
class EateryCuisineFactory extends Factory
{
    protected $model = EateryCuisine::class;

    public function definition()
    {
        return [
            'cuisine' => $this->faker->name(),
        ];
    }
}
