<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Shop\ShopFeedback;
use App\Models\Shop\ShopProduct;

class ShopFeedbackFactory extends Factory
{
    protected $model = ShopFeedback::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'feedback' => $this->faker->sentence,
            'product_id' => Factory::factoryForModel(ShopProduct::class),
        ];
    }

    public function forProduct(ShopProduct $product): self
    {
        return $this->state(fn (): array => [
            'product_id' => $product->id,
        ]);
    }
}
