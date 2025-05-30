<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;

class NationwideBranchFactory extends Factory
{
    protected $model = NationwideBranch::class;

    public function definition()
    {
        return [
            'wheretoeat_id' => 1,
            'name' => $this->faker->word,
            'slug' => $this->faker->slug,
            'town_id' => 1,
            'county_id' => 1,
            'country_id' => 1,
            'address' => str_replace(', ', '<br />', $this->faker->address),
            'lat' => $this->faker->latitude,
            'lng' => $this->faker->longitude,
            'live' => true,
        ];
    }

    public function forEatery(Eatery $eatery): self
    {
        return $this->state(fn () => [
            'wheretoeat_id' => $eatery->id,
        ]);
    }

    public function notLive(): self
    {
        return $this->state(fn () => ['live' => false]);
    }

    public function withOutSlug(): self
    {
        return $this->state(fn () => ['slug' => null]);
    }
}
