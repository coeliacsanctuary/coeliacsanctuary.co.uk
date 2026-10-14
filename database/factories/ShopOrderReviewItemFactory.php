<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderReview;
use App\Models\Shop\ShopOrderReviewItem;
use App\Models\Shop\ShopProduct;

class ShopOrderReviewItemFactory extends Factory
{
    protected $model = ShopOrderReviewItem::class;

    public function definition()
    {
        return [
            'review_id' => self::factoryForModel(ShopOrderReview::class),
            'order_id' => self::factoryForModel(ShopOrder::class),
            'product_type' => ShopProduct::class,
            'product_id' => self::factoryForModel(ShopProduct::class),
            'rating' => $this->faker->numberBetween(1, 5),
            'review' => $this->faker->paragraph,
        ];
    }

    public function forOrder(ShopOrder $order): self
    {
        return $this->state(fn () => [
            'order_id' => $order->id,
        ]);
    }

    public function forReview(ShopOrderReview $review): self
    {
        return $this->state(fn () => [
            'review_id' => $review->id,
        ]);
    }

    public function forProduct(ShopProduct $product): self
    {
        return $this->state(fn () => [
            'product_type' => ShopProduct::class,
            'product_id' => $product->id,
        ]);
    }

    public function forDigitalCard(ShopDigitalCard $digitalCard): self
    {
        return $this->state(fn () => [
            'product_type' => ShopDigitalCard::class,
            'product_id' => $digitalCard->id,
        ]);
    }
}
