<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryTown;
use Illuminate\Support\Str;

class EateryAreaFactory extends Factory
{
    protected $model = EateryArea::class;

    public function definition()
    {
        $area = $this->faker->city;

        return [
            'town_id' => 1,
            'area' => $area,
            'slug' => Str::slug($area),
            'latlng' => '51,-1',
        ];
    }

    public function withoutLatLng(): self
    {
        return $this->state(['latlng' => null]);
    }
}
