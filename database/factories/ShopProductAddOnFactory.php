<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductAddOn;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

class ShopProductAddOnFactory extends Factory
{
    protected $model = ShopProductAddOn::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),

            'product_id' => ShopProduct::factory(),
        ];
    }
}
