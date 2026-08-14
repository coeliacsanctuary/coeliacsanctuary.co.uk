<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EatingOut\EateryRecommendation;
use App\Models\EatingOut\EateryRecommendationAiData;

class EateryRecommendationAiDataFactory extends Factory
{
    protected $model = EateryRecommendationAiData::class;

    public function definition(): array
    {
        return [
            'wheretoeat_place_recommendation_id' => static::factoryForModel(EateryRecommendation::class),
            'place_name' => $this->faker->company,
            'place_address' => $this->faker->address,
            'place_country' => 'England',
            'place_county' => $this->faker->city,
            'place_town' => $this->faker->city,
            'place_area' => null,
            'latitude' => (string) $this->faker->latitude,
            'longitude' => (string) $this->faker->longitude,
            'phone_number' => $this->faker->phoneNumber,
            'website' => $this->faker->url,
            'facebook' => null,
            'instagram' => null,
            'eatery_type' => 'Eatery',
            'venue_type' => 'Restaurant',
            'cuisine' => 'British',
            'info' => $this->faker->paragraph,
            'features' => [],
            'explanation' => $this->faker->paragraph,
            'is_eligible' => true,
        ];
    }

    public function ineligible(): self
    {
        return $this->state(fn () => [
            'is_eligible' => false,
        ]);
    }

    public function on(EateryRecommendation|int $recommendation): self
    {
        return $this->state(fn () => [
            'wheretoeat_place_recommendation_id' => $recommendation instanceof EateryRecommendation ? $recommendation->id : $recommendation,
        ]);
    }
}
