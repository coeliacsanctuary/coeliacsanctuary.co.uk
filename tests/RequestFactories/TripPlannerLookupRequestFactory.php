<?php

declare(strict_types=1);

namespace Tests\RequestFactories;

use Worksome\RequestFactories\RequestFactory;

class TripPlannerLookupRequestFactory extends RequestFactory
{
    public function definition(): array
    {
        return [
            'term' => $this->faker->city(),
            'sessionToken' => $this->faker->uuid(),
        ];
    }
}
