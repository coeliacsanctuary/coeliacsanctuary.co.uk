<?php

declare(strict_types=1);

namespace Tests\RequestFactories\Api\V1;

use App\Models\EatingOut\EateryVenueType;
use Worksome\RequestFactories\RequestFactory;

class RecommendAPlaceRequestFactory extends RequestFactory
{
    public function definition(): array
    {
        return [
            'name' => $this->faker->name,
            'email' => $this->faker->email,
            'place' => [
                'name' => $this->faker->company,
                'location' => $this->faker->address,
                'url' => $this->faker->url,
                'venueType' => EateryVenueType::query()->first()->id ?? 1,
                'details' => $this->faker->paragraph,
            ],
        ];
    }
}
