<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Shop\ShopSource;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shop\ShopSource>
 */
class ShopSourceFactory extends Factory
{
    protected $model = ShopSource::class;

    public function definition()
    {
        return [
            'source' => $this->faker->word(),
        ];
    }
}
