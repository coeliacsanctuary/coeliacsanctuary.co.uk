<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryAttractionRestaurant;

class EateryAttractionRestaurantFactory extends Factory
{
    protected $model = EateryAttractionRestaurant::class;

    public function definition()
    {
        return [
            'wheretoeat_id' => static::factoryForModel(Eatery::class),
            'restaurant_name' => $this->faker->company,
            'info' => $this->faker->paragraph,
        ];
    }
    public function on(Eatery|int $eatery)
    {
        return $this->state(fn (array $attributes) => [
            'wheretoeat_id' => $eatery instanceof Eatery ? $eatery->id : $eatery,
        ]);
    }
}
