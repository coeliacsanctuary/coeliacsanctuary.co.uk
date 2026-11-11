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

    protected function points(?float $viaLat = null): array
    {
        return array_values(array_filter([
            ['label' => 'Start', 'lat' => 53.0, 'lng' => -2.44],
            $viaLat !== null ? ['label' => 'Llangollen, Wales, United Kingdom', 'lat' => $viaLat, 'lng' => -2.44] : null,
            ['label' => 'End', 'lat' => 54.0, 'lng' => -2.44],
        ]));
    }

    #[Test]
    public function itReturnsNoStopsWithoutAnInterval(): void
    {
        $this->assertSame([], $this->callAction(GetTripStopsAction::class, $this->routeTaking(18000), $this->points(), null));
    }

    #[Test]
    public function itPlacesAStopAtEachIntervalAlongTheDrive(): void
    {
        $stops = $this->callAction(GetTripStopsAction::class, $this->routeTaking(18000), $this->points(), 120);

        $this->assertSame([1, 2], array_column($stops, 'number'));
        $this->assertSame([7200.0, 14400.0], array_column($stops, 'duration'));
    }

    #[Test]
    public function itSkipsAStopWithinHalfAnHourOfArriving(): void
    {
        $stops = $this->callAction(GetTripStopsAction::class, $this->routeTaking(15600), $this->points(), 120);

        $this->assertSame([7200.0], array_column($stops, 'duration'));
    }

    #[Test]
    public function itReturnsNoStopsForADriveShorterThanTheInterval(): void
    {
        $this->assertSame([], $this->callAction(GetTripStopsAction::class, $this->routeTaking(3000), $this->points(), 60));
    }

    #[Test]
    public function itGivesEachStopItsPositionOnTheRoute(): void
    {
        $stop = $this->callAction(GetTripStopsAction::class, $this->routeTaking(18000), $this->points(), 180)[0];

        $this->assertEqualsWithDelta(53.6, $stop['lat'], 0.0001);
        $this->assertSame(-2.44, $stop['lng']);
        $this->assertEqualsWithDelta(66717, $stop['distanceAlongRoute'], 50);
    }

    #[Test]
    public function itMarksIntervalStopsAsSuggested(): void
    {
        $stop = $this->callAction(GetTripStopsAction::class, $this->routeTaking(18000), $this->points(), 120)[0];

        $this->assertSame('suggested', $stop['type']);
        $this->assertNull($stop['label']);
    }

    #[Test]
    public function itReturnsViaPointsAsStopsWithoutAnInterval(): void
    {
        $stops = $this->callAction(GetTripStopsAction::class, $this->routeTaking(18000), $this->points(53.5), null);

        $this->assertCount(1, $stops);
        $this->assertSame(['number' => 1, 'type' => 'via', 'label' => 'Llangollen, Wales, United Kingdom'], array_slice($stops[0], 0, 3));
        $this->assertEqualsWithDelta(9000, $stops[0]['duration'], 0.01);
        $this->assertEqualsWithDelta(53.5, $stops[0]['lat'], 0.0001);
    }

    #[Test]
    public function itRestartsTheIntervalAtEachViaPoint(): void
    {
        $stops = $this->callAction(GetTripStopsAction::class, $this->routeTaking(18000), $this->points(53.5), 90);

        $this->assertSame([1, 2, 3], array_column($stops, 'number'));
        $this->assertSame(['suggested', 'via', 'suggested'], array_column($stops, 'type'));
        $this->assertEqualsWithDelta([5400, 9000, 14400], array_column($stops, 'duration'), 0.01);
    }
}
