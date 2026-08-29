<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EatingOut\EateryMagicRouteType;
use App\Models\EatingOut\EateryMagicRouteRecord;
use App\Services\EatingOut\Collection\Configuration;

class EateryMagicRouteRecordFactory extends Factory
{
    protected $model = EateryMagicRouteRecord::class;

    public function definition()
    {
        return [
            'resolver_type' => EateryMagicRouteType::HundredPercentGlutenFree,
            'raw_location' => $this->faker->slug,
            'location_type' => null,
            'location_id' => null,
            'builder_config' => app(Configuration::class),
        ];
    }
}
