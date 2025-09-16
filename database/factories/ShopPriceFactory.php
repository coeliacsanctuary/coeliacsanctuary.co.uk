<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Shop\ShopPrice;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;
use Carbon\Carbon;

class ShopPriceFactory extends Factory
{
    protected $model = ShopPrice::class;

    public function definition()
    {
        return [
            'product_id' => self::factoryForModel(ShopProduct::class),
            'variant_id' => self::factoryForModel(ShopProductVariant::class),
            'price' => $this->faker->numberBetween(100, 1500),
            'start_at' => Carbon::now()->subHour(),
            'sale_price' => false,
        ];
    }

    public function forProduct(ShopProduct $product)
    {
        return $this->state(fn () => [
            'product_id' => $product->id,
            'variant_id' => $product->variants->first()->id,
        ]);
    }

    public function forVariant(ShopProductVariant $variant)
    {
        return $this->state(fn () => [
            'variant_id' => $variant->id,
            'product_id' => $variant->product_id,
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
