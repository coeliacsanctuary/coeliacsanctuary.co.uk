<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Shop\DiscountCodeType;
use App\Models\Shop\ShopDiscountCode;
use App\Models\Shop\ShopDiscountCodesUsed;
use App\Models\Shop\ShopOrder;

class ShopDiscountCodesUsedFactory extends Factory
{
    protected $model = ShopDiscountCodesUsed::class;

    public function definition()
    {
        return [
            'discount_id' => Factory::factoryForModel(ShopDiscountCode::class),
            'order_id' => Factory::factoryForModel(ShopOrder::class),
            'discount_amount' => $this->faker->numberBetween(100, 999),
        ];
    }

    public function forOrder(ShopOrder $order): self
    {
        return $this->state(fn () => [
            'order_id' => $order->id,
        ]);
    }

    public function forDiscountCode(ShopDiscountCode $discountCode): self
    {
        return $this->state(fn () => [
            'discount_id' => $discountCode->id,
        ]);
    }

    public function percentageDiscount(): self
    {
        return $this->state(fn () => [
            'type_id' => DiscountCodeType::PERCENTAGE,
        ]);
    }

    public function moneyDiscount(): self
    {
        return $this->state(fn () => [
            'type_id' => DiscountCodeType::MONEY,
        ]);
    }
}
