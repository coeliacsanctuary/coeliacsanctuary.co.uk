<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Shop\ShopCategory;
use App\Models\Shop\ShopMassDiscount;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shop\ShopMassDiscount>
 */
class ShopMassDiscountFactory extends Factory
{
    protected $model = ShopMassDiscount::class;

    public function definition()
    {
        return [
            'title' => $this->faker->sentence,
            'percentage' => $this->faker->numberBetween(10, 90),
            'start_at' => Carbon::tomorrow(),
            'end_at' => Carbon::tomorrow()->addWeek(),
            'created' => false,
        ];
    }

    public function alreadyProcessed(): self
    {
        return $this->state(['created' => true]);
    }

    public function forCategory(ShopCategory $category): self
    {
        return $this->forCategories([$category->id]);
    }

    public function forCategories(iterable $categories): self
    {
        return $this->afterCreating(fn (ShopMassDiscount $massDiscount) => $massDiscount->assignedCategories()->attach($categories));
    }
}
