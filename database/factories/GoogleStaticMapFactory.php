<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\GoogleStaticMap;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GoogleStaticMap>
 */
class GoogleStaticMapFactory extends Factory
{
    protected $model = GoogleStaticMap::class;

    public function definition()
    {
        return [
            'uuid' => Str::uuid(),
            'latlng' => "{$this->faker->latitude()},{$this->faker->longitude()}",
            'parameters' => md5(json_encode([])),
            'hits' => $this->faker->randomDigit(),
            'last_fetched_at' => now(),
        ];

    }
}
