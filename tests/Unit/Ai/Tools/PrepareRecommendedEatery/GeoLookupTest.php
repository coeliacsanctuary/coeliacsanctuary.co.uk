<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools\PrepareRecommendedEatery;

use App\Ai\Tools\PrepareRecommendedEatery\GeoLookup;
use App\DataObjects\EatingOut\LatLng;
use App\Services\EatingOut\LocationSearchService;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GeoLookupTest extends TestCase
{
    #[Test]
    public function itCallsLocationSearchServiceWithTheGivenAddress(): void
    {
        $this->mock(LocationSearchService::class)
            ->shouldReceive('getLatLng')
            ->once()
            ->withArgs(fn ($term) => (string) $term === '1 Test Street, London')
            ->andReturn(new LatLng(51.5, -0.1));

        (new GeoLookup())->handle(new Request(['address' => '1 Test Street, London']));
    }

    #[Test]
    public function itReturnsLatitudeAndLongitudeAsJson(): void
    {
        $this->mock(LocationSearchService::class)
            ->shouldReceive('getLatLng')
            ->andReturn(new LatLng(51.5074, -0.1278));

        $result = json_decode((string) (new GeoLookup())->handle(new Request(['address' => 'London'])), true);

        $this->assertSame(51.5074, $result['latitude']);
        $this->assertSame(-0.1278, $result['longitude']);
    }

    #[Test]
    public function itHasTheExpectedSchema(): void
    {
        $schema = (new GeoLookup())->schema(new JsonSchemaTypeFactory());

        $this->assertArrayHasKey('address', $schema);
    }
}
