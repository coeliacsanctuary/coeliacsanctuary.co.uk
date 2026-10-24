<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopOrder;
use App\Models\Shop\ShopOrderDigitalItem;

class ShopOrderDigitalItemFactory extends Factory
{
    protected $model = ShopOrderDigitalItem::class;

    public function definition(): array
    {
        return [
            'order_id' => Factory::factoryForModel(ShopOrder::class),
            'digital_card_id' => Factory::factoryForModel(ShopDigitalCard::class),
            'digital_card_title' => $this->faker->words(3, true),
            'digital_card_price' => $this->faker->numberBetween(100, 500),
        ];
    }

    public function inOrder(ShopOrder $order): self
    {
        return $this->state(fn () => ['order_id' => $order->id]);
    }

    public function forCard(ShopDigitalCard $card): self
    {
        return $this->state(fn () => [
            'digital_card_id' => $card->id,
            'digital_card_title' => $card->title,
        ]);
    }
}
