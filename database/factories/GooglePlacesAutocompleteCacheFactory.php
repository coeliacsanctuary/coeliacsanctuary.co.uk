<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\GooglePlacesAutocompleteCache;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GooglePlacesAutocompleteCache>
 */
class GooglePlacesAutocompleteCacheFactory extends Factory
{
    protected $model = GooglePlacesAutocompleteCache::class;

    public function definition(): array
    {
        return [
            'term' => $this->faker->unique()->word(),
            'response' => [['id' => 'ChIJ123', 'label' => 'Crewe, UK']],
            'hits' => 1,
            'last_fetched_at' => now(),
        ];
    }
}
