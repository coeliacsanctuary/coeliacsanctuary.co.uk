<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Search\Search;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Search\Search>
 */
class SearchFactory extends Factory
{
    protected $model = Search::class;

    public function definition(): array
    {
        return [
            'term' => $this->faker->word(),
        ];
    }
}
