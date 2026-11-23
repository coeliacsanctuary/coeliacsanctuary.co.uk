<?php

declare(strict_types=1);

namespace Tests\RequestFactories;

use Worksome\RequestFactories\RequestFactory;

class EateryRecommendAPlaceRequestFactory extends RequestFactory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'place' => [
                'name' => $this->faker->company(),
                'location' => $this->faker->address(),
                'url' => $this->faker->url(),
                'venueType' => 1,
                'details' => $this->faker->paragraph(),
            ],
        ];
    }
}
