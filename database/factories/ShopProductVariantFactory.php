<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Shop\ProductVariantType;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;

class ShopProductVariantFactory extends Factory
{
    protected $model = ShopProductVariant::class;

    public function definition()
    {
        return [
            'live' => true,
            'primary_variant' => false,
            'variant_type' => ProductVariantType::PHYSICAL,
            'title' => $this->faker->words(3, true),
            'weight' => $this->faker->numberBetween(1, 20),
            'quantity' => $this->faker->numberBetween(1, 500),
            'product_id' => self::factoryForModel(ShopProduct::class),
        ];
    }

    public function belongsToProduct(ShopProduct $product): self
    {
        return $this->state([
            'product_id' => $product->id,
        ]);
    }

    public function isPrimary(): self
    {
        return $this->state(['primary_variant' => true]);
    }

    public function notLive(): self
    {
        return $this->state(['live' => false]);
    }

    public function outOfStock(): self
    {
        return $this->state(['quantity' => 0]);
    }
}
