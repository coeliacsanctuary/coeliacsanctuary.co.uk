<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryOpeningTimes;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EatingOut\EateryOpeningTimes>
 */
class EateryOpeningTimesFactory extends Factory
{
    protected $model = EateryOpeningTimes::class;

    public function definition()
    {
        return [
            'wheretoeat_id' => static::factoryForModel(Eatery::class),
            'monday_start' => $this->faker->numberBetween(0, 12) . ':00:00',
            'monday_end' => $this->faker->numberBetween(13, 23) . ':00:00',
            'tuesday_start' => $this->faker->numberBetween(0, 12) . ':00:00',
            'tuesday_end' => $this->faker->numberBetween(13, 23) . ':00:00',
            'wednesday_start' => $this->faker->numberBetween(0, 12) . ':00:00',
            'wednesday_end' => $this->faker->numberBetween(13, 23) . ':00:00',
            'thursday_start' => $this->faker->numberBetween(0, 12) . ':00:00',
            'thursday_end' => $this->faker->numberBetween(13, 23) . ':00:00',
            'friday_start' => $this->faker->numberBetween(0, 12) . ':00:00',
            'friday_end' => $this->faker->numberBetween(13, 23) . ':00:00',
            'saturday_start' => $this->faker->numberBetween(0, 12) . ':00:00',
            'saturday_end' => $this->faker->numberBetween(13, 23) . ':00:00',
            'sunday_start' => $this->faker->numberBetween(0, 12) . ':00:00',
            'sunday_end' => $this->faker->numberBetween(13, 23) . ':00:00',
        ];
    }

    public function forEatery(Eatery $eatery)
    {
        return $this->state(fn (): array => ['wheretoeat_id' => $eatery->id]);
    }
}
