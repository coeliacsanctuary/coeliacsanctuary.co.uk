<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Api\V1\Controllers\EatingOut\Explore;

use App\Actions\EatingOut\CreateSearchAction;
use App\Http\Api\V1\Resources\EatingOut\ExploreEateryResource;
use App\Models\EatingOut\EaterySearchTerm;
use App\Pipelines\EatingOut\GetEateries\GetSearchResultsPipeline;
use App\Services\EatingOut\Filters\GetFiltersForSearchResults;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    #[Test]
    public function itErrorsWithOutASourceHttpHeader(): void
    {
        $this->getJson(route('api.v1.eating-out.explore'))->assertForbidden();
    }

    #[Test]
    public function itErrorsWithoutAOrInvalidSearchTerm(): void
    {
        $this->makeRequest(null)->assertJsonValidationErrorFor('search');
        $this->makeRequest(true)->assertJsonValidationErrorFor('search');
    }

    #[Test]
    public function itCallsTheCreateSearchAction(): void
    {
        $this->mock(CreateSearchAction::class)
            ->shouldReceive('handle')
            ->once()
            ->withArgs(function ($search) {
                $this->assertEquals('foo', $search);

                return true;
            })
            ->andReturn($this->create(EaterySearchTerm::class));

        $this->makeRequest('foo')->assertOk();
    }

    #[Test]
    public function itCallsTheGetSearchResultsPipeline(): void
    {
        $this->mock(GetSearchResultsPipeline::class)
            ->shouldReceive('run')
            ->once()
            ->andReturn(collect()->paginate());

        $this->makeRequest()->assertOk();
    }

    #[Test]
    public function itPassesTheSearchTermThePipeline(): void
    {
        $this->mock(GetSearchResultsPipeline::class)
            ->shouldReceive('run')
            ->withArgs(function ($searchTerm) {
                $this->assertInstanceOf(EaterySearchTerm::class, $searchTerm);
                $this->assertEquals('foo', $searchTerm->term);

                return true;
            })
            ->once()
            ->andReturn(collect()->paginate());

        $this->makeRequest()->assertOk();
    }

    #[Test]
    public function itPassesAnyFiltersToThePipeline(): void
    {
        $this->mock(GetSearchResultsPipeline::class)
            ->shouldReceive('run')
            ->withArgs(function ($searchTerm, $filters) {
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
            ->andReturn(collect()->paginate());

        $requestFilters = [
            'category' => 'eatery,hotel',
            'venueType' => 'restaurant,cafe',
            'feature' => 'parking,wifi',
        ];

        $this->makeRequest(filters: $requestFilters)->assertOk();
    }

    #[Test]
    public function itPassesTheCorrectJsonResourceToThePipeline(): void
    {
        $this->mock(GetSearchResultsPipeline::class)
            ->shouldReceive('run')
            ->withArgs(function ($latlng, $filters, $resource) {
                $this->assertEquals(ExploreEateryResource::class, $resource);

                return true;
            })
            ->once()
            ->andReturn(collect()->paginate());

        $this->makeRequest()->assertOk();
    }

    #[Test]
    public function itCallsTheGetFiltersForSearchResults(): void
    {
        $this->mock(GetFiltersForSearchResults::class)
            ->shouldReceive('usingSearchKey')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('handle')
            ->once()
            ->andReturn([]);

        $this->makeRequest()->assertOk();
    }

    #[Test]
    public function itReturnsADataKey(): void
    {
        $this->mock(GetSearchResultsPipeline::class)
            ->shouldReceive('run')
            ->once()
            ->andReturn(collect()->paginate());

        $this->makeRequest()
            ->assertOk()
            ->assertJsonStructure(['data' => ['eateries' => [], 'filters' => []]]);
    }

    protected function makeRequest(mixed $search = 'foo', string $source = 'bar', array $filters = []): TestResponse
    {
        return $this->getJson(
            route('api.v1.eating-out.explore', ['search' => $search, 'filter' => $filters]),
            ['x-coeliac-source' => $source],
        );
    }
}
