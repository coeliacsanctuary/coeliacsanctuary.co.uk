<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Shop\TravelCardSearchTermHistory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shop\TravelCardSearchTermHistory>
 */
class TravelCardSearchTermHistoryFactory extends Factory
{
    protected $model = TravelCardSearchTermHistory::class;

    public function definition()
    {
        return [
            'term' => $this->faker->word,
            'hits' => 0,
        ];
    }
}
