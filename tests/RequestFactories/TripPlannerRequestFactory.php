<?php

declare(strict_types=1);

namespace Tests\RequestFactories;

use Worksome\RequestFactories\RequestFactory;

class TripPlannerRequestFactory extends RequestFactory
{
    public function definition(): array
    {
        return [
            'points' => [
                ['label' => 'Crewe, England, United Kingdom', 'lat' => 53.1, 'lng' => -2.44],
                ['label' => 'Manchester, England, United Kingdom', 'lat' => 53.48, 'lng' => -2.24],
            ],
            'distance' => 2,
        ];
    }
}
