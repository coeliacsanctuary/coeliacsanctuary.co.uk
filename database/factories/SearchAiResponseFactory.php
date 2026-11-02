<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Search\Search;
use App\Models\Search\SearchAiResponse;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Search\SearchAiResponse>
 */
class SearchAiResponseFactory extends Factory
{
    protected $model = SearchAiResponse::class;

    public function definition(): array
    {
        return [
            'search_id' => Factory::factoryForModel(Search::class),
            'blogs' => $this->faker->numberBetween(0, 100),
            'recipes' => $this->faker->numberBetween(0, 100),
            'eateries' => $this->faker->numberBetween(0, 100),
            'shop' => $this->faker->numberBetween(0, 100),
            'explanation' => $this->faker->sentence(),
            'location' => null,
        ];
    }
}
