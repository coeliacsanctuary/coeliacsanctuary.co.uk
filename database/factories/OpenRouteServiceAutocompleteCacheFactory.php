<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\OpenRouteServiceAutocompleteCache;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OpenRouteServiceAutocompleteCache>
 */
class OpenRouteServiceAutocompleteCacheFactory extends Factory
{
    protected $model = OpenRouteServiceAutocompleteCache::class;

    public function definition(): array
    {
        return [
            'term' => $this->faker->word(),
            'response' => [['id' => '1', 'label' => 'Crewe, England, United Kingdom', 'lat' => 53.1, 'lng' => -2.44]],
            'hits' => 1,
            'most_recent_hit' => now(),
        ];
    }
}
