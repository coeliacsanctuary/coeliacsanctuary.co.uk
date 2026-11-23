<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Shop\TravelCardSearchTerm;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shop\TravelCardSearchTerm>
 */
class TravelCardSearchTermFactory extends Factory
{
    protected $model = TravelCardSearchTerm::class;

    public function definition()
    {
        return [
            'term' => $this->faker->word(),
            'type' => $this->faker->randomElement(['country', 'language']),
            'hits' => 0,
        ];
    }
}
