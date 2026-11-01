<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EatingOut\EaterySearchTerm;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EatingOut\EaterySearchTerm>
 */
class EaterySearchTermFactory extends Factory
{
    protected $model = EaterySearchTerm::class;

    public function definition()
    {
        return [
            'term' => $this->faker->word,
            'range' => $this->faker->randomElement(['1', '2', '5', '10', '20']),
        ];
    }
}
