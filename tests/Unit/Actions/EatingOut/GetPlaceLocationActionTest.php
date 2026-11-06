<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut;

use App\Actions\EatingOut\GetPlaceLocationAction;
use App\Models\GooglePlacesDetailsCache;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Request;
use Saloon\Http\Response;
use SKAgarwal\GoogleApi\PlacesNew\GooglePlaces;
use SKAgarwal\GoogleApi\PlacesNew\Requests\PlaceDetails;
use Tests\TestCase;

class GetPlaceLocationActionTest extends TestCase
{
    protected function mockGoogle(?MockResponse $response = null): MockClient
    {
        $mockClient = new MockClient([
            PlaceDetails::class => $response ?? MockResponse::make(['location' => ['latitude' => 53.0957, 'longitude' => -2.4421]]),
        ]);

        $this->app->instance(GooglePlaces::class, new GooglePlaces()->withMockClient($mockClient));

        return $mockClient;
    }

    #[Test]
    public function itAsksGoogleForOnlyThePlacesLocationWithTheSessionToken(): void
    {
        $mockClient = $this->mockGoogle();

        $this->callAction(GetPlaceLocationAction::class, 'place-1', 'session-token');

        $mockClient->assertSent(fn (Request $request, Response $response): bool => str_ends_with($response->getPendingRequest()->getUrl(), '/places/place-1')
            && $response->getPendingRequest()->query()->all() === ['fields' => 'location', 'sessionToken' => 'session-token']);
    }

    #[Test]
    public function itReturnsThePlacesLatLng(): void
    {
        $this->mockGoogle();

        $latLng = $this->callAction(GetPlaceLocationAction::class, 'place-1', 'token');

        $this->assertSame([53.0957, -2.4421], [$latLng->lat, $latLng->lng]);
    }

    #[Test]
    public function itCachesTheLocationAgainstThePlace(): void
    {
        $this->freezeSecond();

        $this->mockGoogle();

        $this->callAction(GetPlaceLocationAction::class, 'place-1', 'token');

        $cache = GooglePlacesDetailsCache::query()->sole();

        $this->assertSame('place-1', $cache->place_id);
        $this->assertSame(['lat' => 53.0957, 'lng' => -2.4421], $cache->response);
        $this->assertSame(now()->toDateTimeString(), $cache->last_fetched_at->toDateTimeString());
    }

    #[Test]
    public function itReturnsAFreshCachedLocationWithoutCallingGoogleAndRecordsAHit(): void
    {
        $mockClient = $this->mockGoogle();

        $cache = $this->create(GooglePlacesDetailsCache::class, [
            'place_id' => 'place-1',
            'response' => ['lat' => 54.1, 'lng' => -1.5],
            'hits' => 2,
            'last_fetched_at' => now()->subDays(29),
        ]);

        $latLng = $this->callAction(GetPlaceLocationAction::class, 'place-1', 'token');

        $mockClient->assertNothingSent();
        $this->assertSame([54.1, -1.5], [$latLng->lat, $latLng->lng]);
        $this->assertSame(3, $cache->refresh()->hits);
    }

    #[Test]
    public function itRefetchesAndOverwritesACachedLocationOlderThanThirtyDays(): void
    {
        $mockClient = $this->mockGoogle();

        $cache = $this->create(GooglePlacesDetailsCache::class, [
            'place_id' => 'place-1',
            'response' => ['lat' => 54.1, 'lng' => -1.5],
            'last_fetched_at' => now()->subDays(31),
        ]);

        $latLng = $this->callAction(GetPlaceLocationAction::class, 'place-1', 'token');

        $mockClient->assertSentCount(1);
        $this->assertSame([53.0957, -2.4421], [$latLng->lat, $latLng->lng]);
        $this->assertDatabaseCount(GooglePlacesDetailsCache::class, 1);
        $this->assertSame(['lat' => 53.0957, 'lng' => -2.4421], $cache->refresh()->response);
    }

    #[Test]
    public function itThrowsAndCachesNothingWhenGoogleFails(): void
    {
        $this->mockGoogle(MockResponse::make(['error' => ['message' => 'bad']], 404));

        try {
            $this->callAction(GetPlaceLocationAction::class, 'place-1', 'token');

            $this->fail('Expected an exception');
        } catch (RuntimeException) {
            $this->assertDatabaseEmpty(GooglePlacesDetailsCache::class);
        }
    }
}
