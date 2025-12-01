<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Journey\EventType;
use App\Models\Journeys\Event;

class EventFactory extends Factory
{
    protected $model = Event::class;

    public function definition()
    {
        return [
            'event_type' => $this->faker->randomElement(EventType::cases()),
            'element' => $this->faker->word,
        ];
    }
}
