<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api\V1\Controllers\EatingOut\Filters;

use App\Services\EatingOut\Filters\GetFilters;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetControllerTest extends TestCase
{
    #[Test]
    public function itErrorsWithOutASourceHttpHeader(): void
    {
        $this->getJson(route('api.v1.eating-out.filters'))->assertForbidden();
    }

    #[Test]
    public function itCallsTheGetFiltersAction(): void
    {
        $this->mock(GetFilters::class)
            ->shouldReceive('handle')
            ->once()
            ->andReturn([]);

        $this->makeRequest()->assertOk();
    }

    #[Test]
    public function itPassesAnyFiltersToTheAction(): void
    {
        $this->mock(GetFilters::class)
            ->shouldReceive('handle')
            ->withArgs(function ($filters) {
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
            ->andReturn([]);

        $requestFilters = [
            'category' => 'eatery,hotel',
            'venueType' => 'restaurant,cafe',
            'feature' => 'parking,wifi',
        ];

        $this->makeRequest(['filter' => $requestFilters])->assertOk();
    }

    #[Test]
    public function itReturnsADataKey(): void
    {
        $this->mock(GetFilters::class)
            ->shouldReceive('handle')
            ->once()
            ->andReturn([]);

        $this->makeRequest()
            ->assertOk()
            ->assertJsonStructure(['data' => []]);
    }

    protected function makeRequest(array $params = [], string $source = 'foo'): TestResponse
    {
        return $this->getJson(
            route('api.v1.eating-out.filters', $params),
            ['x-coeliac-source' => $source],
        );
    }
}
