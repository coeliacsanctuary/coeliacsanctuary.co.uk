<?php

declare(strict_types=1);

namespace Tests\RequestFactories\Api\V1;

use Worksome\RequestFactories\RequestFactory;

class CreateReviewRequestFactory extends RequestFactory
{
    public function definition(): array
    {
        return [
            'rating' => $this->faker->numberBetween(1, 5),
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'review' => $this->faker->paragraph,
            'food_rating' => $this->faker->randomElement(['poor', 'good', 'excellent']),
            'service_rating' => $this->faker->randomElement(['poor', 'good', 'excellent']),
            'how_expensive' => $this->faker->numberBetween(1, 5),
            'method' => 'app',
        ];
    }
}
