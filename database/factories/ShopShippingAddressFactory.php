<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Shop\ShopCustomer;
use App\Models\Shop\ShopShippingAddress;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shop\ShopShippingAddress>
 */
class ShopShippingAddressFactory extends Factory
{
    protected $model = ShopShippingAddress::class;

    public function definition()
    {
        return [
            'customer_id' => Factory::factoryForModel(ShopCustomer::class),
            'name' => $this->faker->name(),
            'line_1' => $this->faker->buildingNumber(),
            'line_2' => $this->faker->streetAddress(),
            'town' => $this->faker->city(),
            'postcode' => $this->faker->postcode(),
            'country' => 'England',
        ];
    }

    public function to(ShopCustomer $customer): self
    {
        return $this->state(fn (): array => ['customer_id' => $customer->id]);
    }
}
