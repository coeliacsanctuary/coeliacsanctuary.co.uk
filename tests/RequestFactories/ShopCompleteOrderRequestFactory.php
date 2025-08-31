<?php

declare(strict_types=1);

namespace Tests\RequestFactories;

use Worksome\RequestFactories\RequestFactory;

class ShopCompleteOrderRequestFactory extends RequestFactory
{
    public function definition(): array
    {
        return [
            'contact' => [
                'name' => $this->faker->name,
                'email' => $email = $this->faker->email,
                'email_confirmation' => $email,
                'phone' => $this->faker->phoneNumber,
            ],

            'shipping' => [
                'address_1' => $this->faker->streetAddress,
                'address_2' => $this->faker->word,
                'address_3' => $this->faker->word,
                'town' => $this->faker->city,
                'county' => $this->faker->state,
                'postcode' => 'a12bc',
            ],
        ];
    }

    public function digitalOnly(): self
    {
        return $this->without('shipping');
    }
}
