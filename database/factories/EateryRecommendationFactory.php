<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EatingOut\EateryRecommendation;

class EateryRecommendationFactory extends Factory
{
    protected $model = EateryRecommendation::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'place_name' => $this->faker->company,
            'place_location' => $this->faker->address,
            'place_web_address' => $this->faker->url,
            'place_venue_type_id' => 1,
            'place_details' => $this->faker->paragraph,
            'completed' => false,
        ];
    }
}
