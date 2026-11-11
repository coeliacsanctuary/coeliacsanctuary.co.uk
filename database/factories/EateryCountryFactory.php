<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EatingOut\EateryCountry;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EatingOut\EateryCountry>
 */
class EateryCountryFactory extends Factory
{
    protected $model = EateryCountry::class;

    public function definition()
    {
        return [
            'country' => 'England',
        ];
    }
}
