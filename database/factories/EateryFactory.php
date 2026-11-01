<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryType;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EatingOut\Eatery>
 */
class EateryFactory extends Factory
{
    protected $model = Eatery::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'slug' => $this->faker->slug,
            'town_id' => 1,
            'county_id' => 1,
            'country_id' => 1,
            'type_id' => 1,
            'venue_type_id' => 1,
            'cuisine_id' => 1,
            'info' => $this->faker->sentence,
            'address' => str_replace(', ', '<br />', $this->faker->address),
            'phone' => $this->faker->phoneNumber,
            'website' => $this->faker->url,
            'lat' => $this->faker->latitude,
            'lng' => $this->faker->longitude,
            'live' => true,
            'closed_down' => false,
        ];
    }

    public function notLive(): self
    {
        return $this->state(fn (): array => ['live' => false]);
    }

    public function closedDown(): self
    {
        return $this->state(fn (): array => ['closed_down' => true]);
    }

    public function withOutSlug(): self
    {
        return $this->state(fn (): array => ['slug' => null]);
    }

    public function attraction(): self
    {
        return $this->state(fn (): array => ['type_id' => EateryType::ATTRACTION]);
    }

    public function hotel(): self
    {
        return $this->state(fn (): array => ['type_id' => EateryType::HOTEL]);
    }
}
