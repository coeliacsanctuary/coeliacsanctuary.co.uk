<?php

declare(strict_types=1);

namespace Tests\RequestFactories;

use Worksome\RequestFactories\RequestFactory;

class EateryCreateReviewApiRequestFactory extends RequestFactory
{
    public function definition(): array
    {
        return [
            'method' => 'website',
            'rating' => $this->faker->numberBetween(1, 5),
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'food' => $this->faker->randomElement(['poor', 'good', 'excellent']),
            'service' => $this->faker->randomElement(['poor', 'good', 'excellent']),
            'expense' => $this->faker->numberBetween(1, 5),
            'comment' => $this->faker->paragraph(),
        ];
    }

    public function forBranch(): self
    {
        return $this->state([
            'branch_name' => $this->faker->city(),
        ]);
    }
}
