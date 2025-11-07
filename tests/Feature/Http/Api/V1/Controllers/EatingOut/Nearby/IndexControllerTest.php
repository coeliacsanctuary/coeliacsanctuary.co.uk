<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api\V1\Controllers\EatingOut\Nearby;

use App\DataObjects\EatingOut\LatLng;
use App\Http\Api\V1\Resources\EatingOut\NearbyEateryResource;
use App\Pipelines\EatingOut\GetEateries\GetNearbyEateriesPipeline;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    #[Test]
    public function itErrorsWithOutASourceHttpHeader(): void
    {
        $this->getJson(route('api.v1.eating-out.nearby'))->assertForbidden();
    }

    #[Test]
    public function itErrorsWithoutAMissingOrInvalidLatLng(): void
    {
        $this->makeRequest(null)->assertJsonValidationErrorFor('latlng');
    }

    #[Test]
    public function itCallsTheGetNearbyEateriesPipeline(): void
    {
        $this->mock(GetNearbyEateriesPipeline::class)
            ->shouldReceive('run')
            ->once()
            ->andReturn(collect()->paginate());

        $this->makeRequest()->assertOk();
    }

    #[Test]
    public function itPassesTheLatLngToThePipeline(): void
    {
        $this->mock(GetNearbyEateriesPipeline::class)
            ->shouldReceive('run')
            ->withArgs(function ($latlng) {
                $this->assertInstanceOf(LatLng::class, $latlng);
                $this->assertEquals(51, $latlng->lat);
                $this->assertEquals(-1, $latlng->lng);

                return true;
            })
            ->once()
            ->andReturn(collect()->paginate());

        $this->makeRequest()->assertOk();
    }

    #[Test]
    public function itPassesTheCorrectJsonResourceToThePipeline(): void
    {
        $this->mock(GetNearbyEateriesPipeline::class)
            ->shouldReceive('run')
            ->withArgs(function ($latlng, $resource) {
                $this->assertEquals(NearbyEateryResource::class, $resource);

                return true;
            })
            ->once()
            ->andReturn(collect()->paginate());

        $this->makeRequest()->assertOk();
    }

    #[Test]
    public function itReturnsADataKey(): void
    {
        $this->mock(GetNearbyEateriesPipeline::class)
            ->shouldReceive('run')
            ->once()
            ->andReturn(collect()->paginate());

        $this->makeRequest()
            ->assertOk()
            ->assertJsonStructure(['data' => []]);
    }

    protected function makeRequest(mixed $latlng = '51,-1', string $source = 'foo'): TestResponse
    {
        return $this->getJson(
            route('api.v1.eating-out.nearby', ['latlng' => $latlng]),
            ['x-coeliac-source' => $source],
        );
    }
}
