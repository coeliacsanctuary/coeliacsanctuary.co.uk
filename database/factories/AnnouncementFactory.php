<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Announcement;
use Carbon\Carbon;

class AnnouncementFactory extends Factory
{
    protected $model = Announcement::class;

    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(),
            'text' => $this->faker->paragraph(),
            'live' => true,
            'expires_at' => Carbon::now()->addWeek(),
        ];
    }

    public function expired()
    {
        return $this->state(fn () => ['expires_at' => Carbon::now()->subWeek()]);
    }
}
