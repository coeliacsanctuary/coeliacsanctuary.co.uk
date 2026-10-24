<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopDigitalCardPrice;

class ShopDigitalCardPriceFactory extends Factory
{
    protected $model = ShopDigitalCardPrice::class;

    public function definition()
    {
        return [
            'price' => $this->faker->numberBetween(100, 1000),
            'price_with_physical_product' => $this->faker->numberBetween(25, 100),
        ];
    }

    public function forCard(ShopDigitalCard $card): self
    {
        return $this->state(fn () => ['digital_card_id' => $card->id]);
    }
}
