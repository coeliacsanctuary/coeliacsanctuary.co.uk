<?php

declare(strict_types=1);

namespace Tests\RequestFactories;

use Worksome\RequestFactories\RequestFactory;

class MyTravelCardsConfirmOneTimeCodeRequestFactory extends RequestFactory
{
    public function definition(): array
    {
        return [
            'code' => $this->faker->numerify('######'),
        ];
    }
}
