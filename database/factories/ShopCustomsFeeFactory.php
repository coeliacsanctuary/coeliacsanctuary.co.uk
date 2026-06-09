<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Shop\ShopCustomsFee;
use App\Models\Shop\ShopPostageCountry;

class ShopCustomsFeeFactory extends Factory
{
    protected $model = ShopCustomsFee::class;

    public function definition()
    {
        return [
            'postage_country_id' => self::factoryForModel(ShopPostageCountry::class),
            'fee' => $this->faker->numberBetween(100, 200),
            'description' => $this->faker->sentence,
        ];
    }
}
