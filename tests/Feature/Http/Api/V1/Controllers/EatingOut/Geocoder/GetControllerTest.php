<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api\V1\Controllers\EatingOut\Geocoder;

use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Geocoder\Geocoder;
use Tests\TestCase;

class GetControllerTest extends TestCase
{
    #[Test]
    public function itErrorsWithOutASourceHttpHeader(): void
    {
        $this->postJson(route('api.v1.eating-out.geocode'))->assertForbidden();
    }

    #[Test]
    public function itErrorsWithoutATerm(): void
    {
        $this->makeRequest(null)->assertJsonValidationErrorFor('term');
    }

    #[Test]
    public function itCallsTheGetFiltersAction(): void
    {
        $this->mock(Geocoder::class)
            ->shouldReceive('getCoordinatesForAddress')
            ->once()
            ->andReturn(['lat' => 51, 'lng' => -1]);

        $this->makeRequest()->assertOk();
    }

    #[Test]
    public function itReturnsADataKey(): void
    {
        $this->makeRequest()
            ->assertOk()
            ->assertJsonStructure(['data' => ['lat', 'lng']]);
    }

    protected function makeRequest(mixed $term = 'foo', string $source = 'bar'): TestResponse
    {
        return $this->postJson(
            route('api.v1.eating-out.geocode'),
            ['term' => $term],
            ['x-coeliac-source' => $source],
        );
    }
}
