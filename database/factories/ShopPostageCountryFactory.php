<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Shop\PostageArea;
use App\Models\Shop\ShopPostageCountry;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shop\ShopPostageCountry>
 */
class ShopPostageCountryFactory extends Factory
{
    protected $model = ShopPostageCountry::class;

    public function definition()
    {
        return [
            'postage_area_id' => PostageArea::UK->value,
            'country' => 'United Kingdom',
            'iso_code' => 'UK',
        ];
    }
}
