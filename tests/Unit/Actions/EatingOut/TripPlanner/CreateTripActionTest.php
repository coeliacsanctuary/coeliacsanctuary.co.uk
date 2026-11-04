<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut\TripPlanner;

use App\Actions\EatingOut\TripPlanner\CreateTripAction;
use App\Actions\EatingOut\TripPlanner\GetRouteAction;
use App\DataObjects\EatingOut\LatLng;
use App\DataObjects\EatingOut\TripRoute;
use App\Models\EatingOut\EateryTrip;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CreateTripActionTest extends TestCase
{
    protected function points(): array
    {
        return [
            ['label' => 'Crewe, England, United Kingdom', 'lat' => 53.1, 'lng' => -2.44],
            ['label' => 'Leeds, England, United Kingdom', 'lat' => 53.8, 'lng' => -1.55],
            ['label' => 'York, England, United Kingdom', 'lat' => 53.96, 'lng' => -1.08],
        ];
    }

    protected function route(): TripRoute
    {
        return new TripRoute(
            geometry: '_p~iF~ps|U',
            points: collect([new LatLng(38.5, -120.2)]),
            distance: 150000.5,
            duration: 7200.5,
            timings: [['from' => 0, 'to' => 1, 'distance' => 150000.5, 'duration' => 7200.5]],
        );
    }

    #[Test]
    public function itGetsTheRouteForThePointsInOrder(): void
    {
        $this->expectAction(GetRouteAction::class, [
            static fn (Collection $points): bool => $points->map(fn (LatLng $point): array => [$point->lat, $point->lng])->all() === [[53.1, -2.44], [53.8, -1.55], [53.96, -1.08]],
        ], return: $this->route());

        $this->callAction(CreateTripAction::class, $this->points(), 2);
    }

    #[Test]
    public function itStoresTheInputsAndTheRoute(): void
    {
        $this->expectAction(GetRouteAction::class, return: $this->route());

        $trip = $this->callAction(CreateTripAction::class, $this->points(), 3);

        $trip = EateryTrip::query()->findOrFail($trip->id);

        $this->assertSame(
            [['Crewe, England, United Kingdom', 53.1, -2.44], ['Leeds, England, United Kingdom', 53.8, -1.55], ['York, England, United Kingdom', 53.96, -1.08]],
            collect($trip->points)->map(fn (array $point): array => [$point['label'], $point['lat'], $point['lng']])->all(),
        );
        $this->assertSame(3, $trip->distance);
        $this->assertSame('_p~iF~ps|U', $trip->geometry);
        $this->assertSame(150000.5, $trip->route_distance);
        $this->assertSame(7200.5, $trip->route_duration);
        $this->assertSame(
            [[0, 1, 150000.5, 7200.5]],
            collect($trip->timings)->map(fn (array $timing): array => [$timing['from'], $timing['to'], $timing['distance'], $timing['duration']])->all(),
        );
    }
}
