<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Popup;

class PopupFactory extends Factory
{
    protected $model = Popup::class;

    public function definition(): array
    {
        return [
            'text' => $this->faker->text(),
            'link' => $this->faker->word(),
            'display_every' => $this->faker->randomDigitNotZero(),
            'live' => true,
        ];
    }
}
