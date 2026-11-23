<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\GooglePlacesDetailsCache;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GooglePlacesDetailsCache>
 */
class GooglePlacesDetailsCacheFactory extends Factory
{
    protected $model = GooglePlacesDetailsCache::class;

    public function definition(): array
    {
        return [
            'place_id' => $this->faker->unique()->uuid(),
            'response' => ['lat' => 53.1, 'lng' => -2.44],
            'hits' => 1,
            'last_fetched_at' => now(),
        ];
    }
}
