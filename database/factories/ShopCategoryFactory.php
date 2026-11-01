<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Shop\ShopCategory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shop\ShopCategory>
 */
class ShopCategoryFactory extends Factory
{
    protected $model = ShopCategory::class;

    public function definition()
    {
        return [
            'title' => $this->faker->name(),
            'description' => $this->faker->sentence(),
            'slug' => $this->faker->slug(2),
            'meta_keywords' => $this->faker->sentence(),
            'meta_description' => $this->faker->sentence(),
        ];
    }
}
