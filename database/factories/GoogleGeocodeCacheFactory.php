<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\GoogleGeocodeCache;

class GoogleGeocodeCacheFactory extends Factory
{
    protected $model = GoogleGeocodeCache::class;

    public function definition(): array
    {
        return [
            'term' => $this->faker->word(),
            'response' => '{"lat":51,"lng":-1}',
            'hits' => 1,
            'most_recent_hit' => now(),
        ];
    }
}
