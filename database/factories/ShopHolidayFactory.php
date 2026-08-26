<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Shop\ShopHoliday;

class ShopHolidayFactory extends Factory
{
    protected $model = ShopHoliday::class;

    public function definition()
    {
        return [
            'start_date' => today()->subDay(),
            'end_date' => today()->addDay(),
            'ship_on' => today()->addDays(2),
        ];
    }

    public function upcoming(): self
    {
        return $this->state([
            'start_date' => today()->addWeek(),
            'end_date' => today()->addWeeks(2),
            'ship_on' => today()->addWeeks(2)->addDay(),
        ]);
    }

    public function expired(): self
    {
        return $this->state([
            'start_date' => today()->subWeeks(2),
            'end_date' => today()->subWeek(),
            'ship_on' => today()->subWeek()->addDay(),
        ]);
    }
}
