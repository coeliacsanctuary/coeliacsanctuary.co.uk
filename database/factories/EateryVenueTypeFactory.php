<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EatingOut\EateryVenueType;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EatingOut\EateryVenueType>
 */
class EateryVenueTypeFactory extends Factory
{
    protected $model = EateryVenueType::class;

    public function definition()
    {
        return [
            'venue_type' => $this->faker->word,
            'slug' => $this->faker->slug,
            'type_id' => 1,
        ];
    }
}
