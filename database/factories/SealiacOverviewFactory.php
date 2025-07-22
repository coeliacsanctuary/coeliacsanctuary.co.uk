<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Models\SealiacOverview;
use App\Models\Shop\ShopProduct;

class SealiacOverviewFactory extends Factory
{
    protected $model = SealiacOverview::class;

    public function definition()
    {
        return [
            'model_id' => 1,
            'model_type' => $this->faker->randomElement([Eatery::class, NationwideBranch::class, ShopProduct::class]),
            'overview' => $this->faker->paragraphs(3, true),
            'invalidated' => false,
            'thumbs_up' => 0,
            'thumbs_down' => 0,
        ];
    }

    public function forEatery(Eatery $eatery)
    {
        return $this->state(fn () => [
            'model_id' => $eatery->id,
            'model_type' => Eatery::class,
        ]);
    }

    public function forNationwideBranch(NationwideBranch $branch)
    {
        return $this->state(fn () => [
            'model_type' => NationwideBranch::class,
            'model_id' => $branch->id,
        ]);
    }

    public function forProduct(ShopProduct $product)
    {
        return $this->state(fn () => [
            'model_type' => ShopProduct::class,
            'model_id' => $product->id,
        ]);
    }

    public function invalidated()
    {
        return $this->state(fn () => [
            'invalidated' => true,
        ]);
    }
}
