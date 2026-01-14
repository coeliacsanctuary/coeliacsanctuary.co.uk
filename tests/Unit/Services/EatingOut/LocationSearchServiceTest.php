<?php

declare(strict_types=1);

namespace Tests\Unit\Services\EatingOut;

use App\Models\GoogleGeocodeCache;
use App\Services\EatingOut\LocationSearchService;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Geocoder\Geocoder;
use Tests\TestCase;

class LocationSearchServiceTest extends TestCase
{
    #[Test]
    public function itCanGetTheLatLngForAResult(): void
    {
        $london = ['lat' => 51.5, 'lng' => -0.1];

        $this->mock(Geocoder::class)
            ->shouldReceive('getAllCoordinatesForAddress')
            ->withArgs(function (string $term) {
                $this->assertEquals('London', $term);

                return true;
            })
            ->once()
            ->andReturn([
                $london,
            ]);

        $latLng = app(LocationSearchService::class)->getLatLng('London');

        $this->assertEquals($london['lat'], $latLng->lat);
        $this->assertEquals($london['lng'], $latLng->lng);
    }

    #[Test]
    public function ifTheResultIsCachedItWillReturnThat(): void
    {
        $edinburgh = ['lat' => 55.95, 'lng' => -3.18];

        $this->create(GoogleGeocodeCache::class, [
            'term' => 'Edinburgh',
            'response' => $edinburgh,
        ]);

        $this->mock(Geocoder::class)->shouldNotReceive('getAllCoordinatesForAddress');

        $latLng = app(LocationSearchService::class)->getLatLng('Edinburgh');

        $this->assertEquals($edinburgh['lat'], $latLng->lat);
        $this->assertEquals($edinburgh['lng'], $latLng->lng);
    }

    #[Test]
    public function itUpdatesTheCachedResult(): void
    {
        $edinburgh = ['lat' => 55.95, 'lng' => -3.18];

        $result = $this->create(GoogleGeocodeCache::class, [
            'term' => 'Edinburgh',
            'response' => $edinburgh,
            'hits' => 5,
            'most_recent_hit' => now()->subDays(10),
        ]);

        $this->mock(Geocoder::class)->shouldNotReceive('getAllCoordinatesForAddress');

        app(LocationSearchService::class)->getLatLng('Edinburgh');

        $result->refresh();

        $this->assertEquals(6, $result->hits);
        $this->assertTrue($result->most_recent_hit->isToday());
    }

    #[Test]
    public function itCachesTheRawResultFromGoogle(): void
    {
        $london = ['lat' => 51.5, 'lng' => -0.1];

        $this->mock(Geocoder::class)
            ->shouldReceive('getAllCoordinatesForAddress')
            ->withArgs(function (string $term) {
                $this->assertEquals('London', $term);

                return true;
            })
            ->once()
            ->andReturn([
                $london,
            ]);

        $this->assertDatabaseEmpty(GoogleGeocodeCache::class);

        app(LocationSearchService::class)->getLatLng('London');

        $this->assertDatabaseCount(GoogleGeocodeCache::class, 1);

        $cache = GoogleGeocodeCache::query()->first();

        $this->assertEquals('London', $cache->term);
        $this->assertSame($london, $cache->response);
        $this->assertEquals(1, $cache->hits);
    }
}
