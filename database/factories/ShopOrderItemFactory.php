<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderItem;
use App\Models\Shop\ShopProduct;
use App\Models\Shop\ShopProductVariant;

class ShopOrderItemFactory extends Factory
{
    protected $model = ShopOrderItem::class;

    public function definition()
    {
        return [
            'order_id' => Factory::factoryForModel(ShopOrder::class),
            'product_id' => Factory::factoryForModel(ShopProduct::class),
            'product_variant_id' => Factory::factoryForModel(ShopProductVariant::class),
            'product_title' => $this->faker->words(3, true),
            'product_price' => $this->faker->numberBetween(100, 500),
            'quantity' => 1,
        ];
    }

    public function inOrder(ShopOrder $order): self
    {
        return $this->state(fn () => ['order_id' => $order->id]);
    }

    public function toBasket(ShopOrder $basket): self
    {
        return $this->inOrder($basket);
    }

    public function inProduct(ShopProduct $product): self
    {
        return $this->state(fn () => ['product_id' => $product->id]);
    }

    public function inVariant(ShopProductVariant $variant): self
    {
        return $this->state(fn () => ['product_variant_id' => $variant->id]);
    }

    public function add(ShopProductVariant $productVariant, int $quantity = 1): self
    {
        return $this->state(fn () => [
            'product_id' => $productVariant->product->id,
            'product_variant_id' => $productVariant->id,
            'product_title' => $productVariant->product->title,
            'product_price' => $productVariant->currentPrice,
            'quantity' => $quantity,
        ]);
    }
}
