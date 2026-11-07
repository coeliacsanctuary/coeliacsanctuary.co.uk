<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut\TripPlanner;

use App\Actions\EatingOut\TripPlanner\GetRouteAction;
use App\Actions\EatingOut\TripPlanner\UpdateTripAction;
use App\DataObjects\EatingOut\LatLng;
use App\DataObjects\EatingOut\TripRoute;
use App\Enums\EatingOut\TripSummaryStatus;
use App\Jobs\EatingOut\GenerateTripSummaryJob;
use App\Models\EatingOut\EateryTrip;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateTripActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();
    }

    protected function points(): array
    {
        return [
            ['label' => 'York, England, United Kingdom', 'lat' => 53.96, 'lng' => -1.08],
            ['label' => 'Scarborough, England, United Kingdom', 'lat' => 54.28, 'lng' => -0.4],
            ['label' => 'Whitby, England, United Kingdom', 'lat' => 54.49, 'lng' => -0.61],
        ];
    }

    protected function route(): TripRoute
    {
        return new TripRoute(
            geometry: '_ulLnnqC',
            points: collect([new LatLng(40.7, -120.95)]),
            distance: 90000.5,
            duration: 5400.5,
            timings: [['from' => 0, 'to' => 1, 'distance' => 90000.5, 'duration' => 5400.5]],
        );
    }

    #[Test]
    public function itGetsTheRouteForTheNewPointsInOrder(): void
    {
        $trip = $this->create(EateryTrip::class);

        $this->expectAction(GetRouteAction::class, [
            static fn (Collection $points): bool => $points->map(fn (LatLng $point): array => [$point->lat, $point->lng])->all() === [[53.96, -1.08], [54.28, -0.4], [54.49, -0.61]],
        ], return: $this->route());

        $this->callAction(UpdateTripAction::class, $trip, $this->points(), 5);
    }

    #[Test]
    public function itUpdatesTheTripsInputsAndRoute(): void
    {
        $trip = $this->create(EateryTrip::class);

        $this->expectAction(GetRouteAction::class, return: $this->route());

        $this->callAction(UpdateTripAction::class, $trip, $this->points(), 5);

        $trip->refresh();

        $this->assertSame(
            [['York, England, United Kingdom', 53.96, -1.08], ['Scarborough, England, United Kingdom', 54.28, -0.4], ['Whitby, England, United Kingdom', 54.49, -0.61]],
            collect($trip->points)->map(fn (array $point): array => [$point['label'], $point['lat'], $point['lng']])->all(),
        );
        $this->assertSame(5, $trip->distance);
        $this->assertSame('_ulLnnqC', $trip->geometry);
        $this->assertSame(90000.5, $trip->route_distance);
        $this->assertSame(5400.5, $trip->route_duration);
        $this->assertSame(
            [[0, 1, 90000.5, 5400.5]],
            collect($trip->timings)->map(fn (array $timing): array => [$timing['from'], $timing['to'], $timing['distance'], $timing['duration']])->all(),
        );
    }

    #[Test]
    public function itKeepsTheSameKeyAndEditToken(): void
    {
        $trip = $this->create(EateryTrip::class);
        [$key, $token] = [$trip->key, $trip->edit_token];

        $this->expectAction(GetRouteAction::class, return: $this->route());

        $this->callAction(UpdateTripAction::class, $trip, $this->points(), 5);

        $trip->refresh();

        $this->assertSame($key, $trip->key);
        $this->assertSame($token, $trip->edit_token);
        $this->assertDatabaseCount(EateryTrip::class, 1);
    }

    #[Test]
    public function itMovesTheUpdatedTimeOn(): void
    {
        $this->travelTo('2026-01-01 10:00:00');

        $trip = $this->create(EateryTrip::class);

        $this->travelTo('2026-02-01 12:00:00');

        $this->expectAction(GetRouteAction::class, return: $this->route());

        $this->callAction(UpdateTripAction::class, $trip, $this->points(), 5);

        $this->assertSame('2026-02-01 12:00:00', $trip->refresh()->updated_at?->toDateTimeString());
    }

    #[Test]
    public function itStoresTheStopInterval(): void
    {
        $trip = $this->create(EateryTrip::class);

        $this->expectAction(GetRouteAction::class, return: $this->route());

        $this->callAction(UpdateTripAction::class, $trip, $this->points(), 5, 180);

        $this->assertSame(180, $trip->refresh()->stop_interval);
    }

    #[Test]
    public function itResetsTheSummaryToPending(): void
    {
        $trip = $this->build(EateryTrip::class)->withSummary()->create();

        $this->expectAction(GetRouteAction::class, return: $this->route());

        $this->callAction(UpdateTripAction::class, $trip, $this->points(), 5);

        $trip->refresh();

        $this->assertNull($trip->ai_summary);
        $this->assertSame(TripSummaryStatus::PENDING, $trip->ai_summary_status);
    }

    #[Test]
    public function itDispatchesTheJobToGenerateTheSummary(): void
    {
        $trip = $this->build(EateryTrip::class)->withSummary()->create();

        $this->expectAction(GetRouteAction::class, return: $this->route());

        $this->callAction(UpdateTripAction::class, $trip, $this->points(), 5);

        Bus::assertDispatched(GenerateTripSummaryJob::class);
    }
}
