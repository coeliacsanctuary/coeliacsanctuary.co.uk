<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut\TripPlanner;

use App\Actions\EatingOut\TripPlanner\GetTripStopsAction;
use App\DataObjects\EatingOut\LatLng;
use App\DataObjects\EatingOut\TripRoute;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetTripStopsActionTest extends TestCase
{
    /** A straight route due north from 53.00,-2.44 with 101 evenly spaced points, taking the given number of seconds. */
    protected function routeTaking(float $seconds): TripRoute
    {
        return new TripRoute(
            geometry: '',
            points: collect(range(0, 100))->map(fn (int $index): LatLng => new LatLng(53 + ($index / 100), -2.44)),
            distance: 111195,
            duration: $seconds,
            timings: [['from' => 0, 'to' => 100, 'distance' => 111195, 'duration' => $seconds]],
        );
    }

    #[Test]
    public function itReturnsNoStopsWithoutAnInterval(): void
    {
        $this->assertSame([], $this->callAction(GetTripStopsAction::class, $this->routeTaking(18000), null));
    }

    #[Test]
    public function itPlacesAStopAtEachIntervalAlongTheDrive(): void
    {
        $stops = $this->callAction(GetTripStopsAction::class, $this->routeTaking(18000), 120);

        $this->assertSame([1, 2], array_column($stops, 'number'));
        $this->assertSame([7200.0, 14400.0], array_column($stops, 'duration'));
    }

    #[Test]
    public function itSkipsAStopWithinHalfAnHourOfArriving(): void
    {
        $stops = $this->callAction(GetTripStopsAction::class, $this->routeTaking(15600), 120);

        $this->assertSame([7200.0], array_column($stops, 'duration'));
    }

    #[Test]
    public function itReturnsNoStopsForADriveShorterThanTheInterval(): void
    {
        $this->assertSame([], $this->callAction(GetTripStopsAction::class, $this->routeTaking(3000), 60));
    }

    #[Test]
    public function itGivesEachStopItsPositionOnTheRoute(): void
    {
        $stop = $this->callAction(GetTripStopsAction::class, $this->routeTaking(18000), 180)[0];

        $this->assertEqualsWithDelta(53.6, $stop['lat'], 0.0001);
        $this->assertSame(-2.44, $stop['lng']);
        $this->assertEqualsWithDelta(66717, $stop['distanceAlongRoute'], 50);
    }
}
