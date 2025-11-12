<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api\V1\Controllers\EatingOut\Browse;

use App\DataObjects\EatingOut\LatLng;
use App\Http\Api\V1\Resources\EatingOut\EateryBrowseResource;
use App\Pipelines\EatingOut\GetEateries\BrowseEateriesPipeline;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    #[Test]
    public function itErrorsWithOutASourceHttpHeader(): void
    {
        $this->getJson(route('api.v1.eating-out.browse'))->assertForbidden();
    }

    #[Test]
    public function itErrorsWithoutAMissingOrInvalidLat(): void
    {
        $this->makeRequest($this->paramFactory(lat: null))->assertJsonValidationErrorFor('lat');
        $this->makeRequest($this->paramFactory(lat: 'foo'))->assertJsonValidationErrorFor('lat');
    }

    #[Test]
    public function itErrorsWithoutAMissingOrInvalidLng(): void
    {
        $this->makeRequest($this->paramFactory(lng: null))->assertJsonValidationErrorFor('lng');
        $this->makeRequest($this->paramFactory(lng: 'foo'))->assertJsonValidationErrorFor('lng');
    }

    #[Test]
    public function itErrorsWithoutAMissingOrInvalidRadius(): void
    {
        $this->makeRequest($this->paramFactory(radius: null))->assertJsonValidationErrorFor('radius');
        $this->makeRequest($this->paramFactory(radius: 'foo'))->assertJsonValidationErrorFor('radius');
    }

    #[Test]
    public function itCallsTheBrowseEateriesPipeline(): void
    {
        $this->mock(BrowseEateriesPipeline::class)
            ->shouldReceive('run')
            ->once()
            ->andReturn(collect());

        $this->makeRequest($this->paramFactory())->assertOk();
    }

    #[Test]
    public function itPassesTheLatLngToThePipeline(): void
    {
        $this->mock(BrowseEateriesPipeline::class)
            ->shouldReceive('run')
            ->withArgs(function ($latlng) {
                $this->assertInstanceOf(LatLng::class, $latlng);
                $this->assertEquals(51, $latlng->lat);
                $this->assertEquals(-1, $latlng->lng);
                $this->assertEquals(5, $latlng->radius);

                return true;
            })
            ->once()
            ->andReturn(collect());

        $this->makeRequest($this->paramFactory())->assertOk();
    }

    #[Test]
    public function itPassesAnyFiltersToThePipeline(): void
    {
        $this->mock(BrowseEateriesPipeline::class)
            ->shouldReceive('run')
            ->withArgs(function ($latlng, $filters) {
                $this->assertIsArray($filters);
                $this->assertArrayHasKeys(['categories', 'venueTypes', 'features'], $filters);

                $this->assertIsArray($filters['categories']);
                $this->assertIsArray($filters['venueTypes']);
                $this->assertIsArray($filters['features']);

                $this->assertEquals(['eatery', 'hotel'], $filters['categories']);
                $this->assertEquals(['restaurant', 'cafe'], $filters['venueTypes']);
                $this->assertEquals(['parking', 'wifi'], $filters['features']);

                return true;
            })
            ->once()
            ->andReturn(collect());

        $requestFilters = [
            'category' => 'eatery,hotel',
            'venueType' => 'restaurant,cafe',
            'feature' => 'parking,wifi',
        ];

        $this->makeRequest($this->paramFactory(filters: $requestFilters))->assertOk();
    }

    #[Test]
    public function itPassesTheCorrectJsonResourceToThePipeline(): void
    {
        $this->mock(BrowseEateriesPipeline::class)
            ->shouldReceive('run')
            ->withArgs(function ($latlng, $filters, $resource) {
                $this->assertEquals(EateryBrowseResource::class, $resource);

                return true;
            })
            ->once()
            ->andReturn(collect());

        $this->makeRequest($this->paramFactory())->assertOk();
    }

    #[Test]
    public function itReturnsADataKey(): void
    {
        $this->mock(BrowseEateriesPipeline::class)
            ->shouldReceive('run')
            ->once()
            ->andReturn(collect());

        $this->makeRequest($this->paramFactory())
            ->assertOk()
            ->assertJsonStructure(['data' => []]);
    }

    protected function makeRequest(array $params, string $source = 'foo'): TestResponse
    {
        return $this->getJson(
            route('api.v1.eating-out.browse', $params),
            ['x-coeliac-source' => $source],
        );
    }

    protected function paramFactory($lat = 51, $lng = -1, $radius = 5, $filters = [])
    {
        return [
            'lat' => $lat,
            'lng' => $lng,
            'radius' => $radius,
            'filter' => $filters,
        ];
    }
}
