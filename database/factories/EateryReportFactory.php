<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryReport;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EatingOut\EateryReport>
 */
class EateryReportFactory extends Factory
{
    protected $model = EateryReport::class;

    public function definition()
    {
        return [
            'wheretoeat_id' => static::factoryForModel(Eatery::class),
            'details' => $this->faker->paragraph(),
            'completed' => false,
            'ignored' => false,
        ];
    }
}
