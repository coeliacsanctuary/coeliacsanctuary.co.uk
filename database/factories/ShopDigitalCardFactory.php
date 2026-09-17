<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Shop\DigitalCardType;
use App\Models\Shop\ShopDigitalCard;
use App\Models\Shop\ShopProduct;
use Illuminate\Support\Str;

class ShopDigitalCardFactory extends Factory
{
    protected $model = ShopDigitalCard::class;

    public function definition()
    {
        $language = $this->faker->unique()->word;

        return [
            'type' => DigitalCardType::STANDARD,
            'title' => "{$language} Travel Card",
            'slug' => Str::slug("{$language} travel card"),
            'language' => $language,
            'native_language' => $language,
            'live' => true,
            'enable_physical_addons' => false,
            'card_title' => $this->faker->sentence,
            'card_intro' => $this->faker->paragraph,
            'cant_eat_label' => 'I cant eat',
            'cant_eat_items' => $this->faker->sentence,
            'can_eat_label' => 'I can eat',
            'can_eat_items' => $this->faker->sentence,
            'card_outro' => $this->faker->paragraph,
            'card_thank_you' => $this->faker->sentence,
        ];
    }

    public function forProduct(ShopProduct $product): self
    {
        return $this->state(fn () => ['product_id' => $product->id]);
    }

    public function notLive(): self
    {
        return $this->state(fn () => ['live' => false]);
    }
}
