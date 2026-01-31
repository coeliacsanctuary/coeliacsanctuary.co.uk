<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductAddOn;

class ShopProductAddOnFactory extends Factory
{
    protected $model = ShopProductAddOn::class;

    public function definition(): array
    {
        return [
            'product_id' => Factory::factoryForModel(ShopProduct::class),
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
        ];
    }

    public function forProduct(ShopProduct $product): self
    {
        return $this->state(fn () => [
            'product_id' => $product->id,
        ]);
    }
}
