<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Shop\PostageArea;
use App\Enums\Shop\ShippingMethod;
use App\Models\Shop\ShopPostagePrice;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shop\ShopPostagePrice>
 */
class ShopPostagePriceFactory extends Factory
{
    protected $model = ShopPostagePrice::class;

    public function definition()
    {
        return [
            'postage_country_area_id' => PostageArea::UK,
            'shipping_method_id' => ShippingMethod::LETTER,
            'max_weight' => $this->faker->numberBetween(10, 150),
            'price' => $this->faker->numberBetween(100, 200),
        ];
    }
}
