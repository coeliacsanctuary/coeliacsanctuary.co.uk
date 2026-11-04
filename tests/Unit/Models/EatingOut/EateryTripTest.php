<?php

declare(strict_types=1);

namespace Tests\Unit\Models\EatingOut;

use App\DataObjects\EatingOut\LatLng;
use App\Models\EatingOut\EateryTrip;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryTripTest extends TestCase
{
    #[Test]
    public function itGeneratesAnEightCharacterAlphanumericKey(): void
    {
        $trip = $this->create(EateryTrip::class);

        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{8}$/', $trip->key);
    }

    #[Test]
    public function itGeneratesAnEditToken(): void
    {
        $trip = $this->create(EateryTrip::class);

        $this->assertMatchesRegularExpression('/^[A-Za-z0-9]{40}$/', $trip->edit_token);
    }

    #[Test]
    public function itGeneratesANewKeyIfTheFirstIsAlreadyTaken(): void
    {
        $this->create(EateryTrip::class)->forceFill(['key' => 'TAKEN123'])->saveQuietly();

        $attempts = ['TAKEN123', 'FREE1234'];

        Str::createRandomStringsUsing(function (int $length) use (&$attempts): string {
            return $length === 8 ? array_shift($attempts) : str_repeat('a', $length);
        });

        $trip = $this->create(EateryTrip::class);

        Str::createRandomStringsNormally();

        $this->assertSame('FREE1234', $trip->key);
    }

    #[Test]
    public function itUsesTheKeyAsTheRouteKey(): void
    {
        $trip = $this->create(EateryTrip::class);

        $this->assertSame($trip->key, $trip->getRouteKey());
    }

    #[Test]
    public function itConvertsToATripRoute(): void
    {
        $trip = $this->create(EateryTrip::class);

        $route = $trip->toTripRoute();

        $this->assertSame('_p~iF~ps|U_ulLnnqC_mqNvxq`@', $route->geometry);
        $this->assertSame([[38.5, -120.2], [40.7, -120.95], [43.252, -126.453]], $route->points->map(fn (LatLng $point): array => [$point->lat, $point->lng])->all());
        $this->assertSame(57381.5, $route->distance);
        $this->assertSame(3222.9, $route->duration);
        $this->assertSame([['from' => 0, 'to' => 2, 'distance' => 57381.5, 'duration' => 3222.9]], $route->timings);
    }

    #[Test]
    public function recordingAVisitSetsTheLastVisitedTimeWithoutChangingTheUpdatedTime(): void
    {
        $this->travelTo('2026-01-01 10:00:00');

        $trip = $this->create(EateryTrip::class);

        $this->travelTo('2026-02-01 12:00:00');

        $trip->recordVisit();

        $trip->refresh();

        $this->assertSame('2026-02-01 12:00:00', $trip->last_visited_at?->toDateTimeString());
        $this->assertSame('2026-01-01 10:00:00', $trip->updated_at?->toDateTimeString());
    }

    #[Test]
    public function itCanBeEditedWithItsEditToken(): void
    {
        $trip = $this->create(EateryTrip::class);

        $this->assertTrue($trip->canBeEditedWith($trip->edit_token));
    }

    #[Test]
    public function itCannotBeEditedWithADifferentToken(): void
    {
        $trip = $this->create(EateryTrip::class);

        $this->assertFalse($trip->canBeEditedWith('not-the-token'));
    }

    #[Test]
    public function itCannotBeEditedWithoutAToken(): void
    {
        $trip = $this->create(EateryTrip::class);

        $this->assertFalse($trip->canBeEditedWith(null));
    }
}
