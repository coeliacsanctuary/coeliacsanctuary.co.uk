<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Shop\ShopCustomer;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shop\ShopCustomer>
 */
class ShopCustomerFactory extends Factory
{
    protected $model = ShopCustomer::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'phone' => $this->faker->phoneNumber(),
        ];
    }
}
