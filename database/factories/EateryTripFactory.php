<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\EatingOut\TripSummaryStatus;
use App\Models\EatingOut\EateryTrip;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EatingOut\EateryTrip>
 */
class EateryTripFactory extends Factory
{
    protected $model = EateryTrip::class;

    public function definition(): array
    {
        return [
            'points' => [
                ['label' => 'Crewe, England, United Kingdom', 'lat' => 53.1, 'lng' => -2.44],
                ['label' => 'Manchester, England, United Kingdom', 'lat' => 53.48, 'lng' => -2.24],
            ],
            'distance' => 2,
            'stop_interval' => null,
            'geometry' => '_p~iF~ps|U_ulLnnqC_mqNvxq`@',
            'route_distance' => 57381.5,
            'route_duration' => 3222.9,
            'timings' => [['from' => 0, 'to' => 2, 'distance' => 57381.5, 'duration' => 3222.9]],
        ];
    }

    public function withSummary(): self
    {
        return $this->state(fn (): array => [
            'ai_summary' => [
                'overview' => 'An overview of the trip.',
                'stops' => [],
            ],
            'ai_summary_status' => TripSummaryStatus::READY,
        ]);
    }
}
