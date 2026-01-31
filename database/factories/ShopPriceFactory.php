<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopPrice;
use Carbon\Carbon;

class ShopPriceFactory extends Factory
{
    protected $model = ShopPrice::class;

    public function definition()
    {
        return [
            'purchasable_type' => ShopProduct::class,
            'purchasable_id' => self::factoryForModel(ShopProduct::class),
            'price' => $this->faker->numberBetween(100, 1500),
            'start_at' => Carbon::now()->subHour(),
            'sale_price' => false,
        ];
    }

    public function forProduct(ShopProduct $product): static
    {
        return $this->state(fn () => [
            'purchasable_type' => ShopProduct::class,
            'purchasable_id' => $product->id,
        ]);
    }

    public function onSale()
    {
        return $this->state(fn () => [
            'sale_price' => true,
        ]);
    }

    public function ended()
    {
        return $this->state(fn () => [
            'end_at' => Carbon::now()->subDay(),
        ]);
    }
}
