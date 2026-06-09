<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools\AskSealiac;

use App\Ai\State\ChatContext;
use App\Ai\Tools\AskSealiac\SearchEateriesBySearchTermTool;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Pipelines\EatingOut\GetEateries\GetEateriesForAskSealiacSearchPipeline;
use Database\Seeders\EateryScaffoldingSeeder;
use Laravel\Ai\Tools\Request;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SearchEateriesBySearchTermToolTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);
    }

    protected function tearDown(): void
    {
        ChatContext::clear();

        parent::tearDown();
    }

    #[Test]
    public function itReturnsEateriesForASearchTerm(): void
    {
        $county = $this->create(EateryCounty::class, ['country_id' => 1]);
        $town = $this->create(EateryTown::class, ['county_id' => $county->id]);

        $eatery = $this->create(Eatery::class, [
            'name' => 'Test Restaurant',
            'town_id' => $town->id,
            'county_id' => $county->id,
            'country_id' => 1,
            'address' => '123 High Street',
        ]);

        $eatery->distance = 2.5;
        $eatery->load(['county', 'town', 'area', 'venueType', 'type', 'cuisine', 'restaurants', 'features', 'reviews']);

        $pipeline = Mockery::mock(GetEateriesForAskSealiacSearchPipeline::class);
        $pipeline->shouldReceive('run')
            ->once()
            ->withArgs(fn (string $term, int $radius, array $filters, string $sort) => $term === 'London' && $radius === 5 && $sort === 'distance')
            ->andReturn(collect([$eatery]));

        $this->app->instance(GetEateriesForAskSealiacSearchPipeline::class, $pipeline);

        $tool = new SearchEateriesBySearchTermTool();
        $result = json_decode((string) $tool->handle(new Request([
            'term' => 'London',
            'radius' => 5,
            'sort' => 'distance',
            'type' => [],
            'venueTypes' => [],
            'features' => [],
        ])), true);

        $this->assertCount(1, $result);
        $this->assertEquals('Test Restaurant', $result[0]['name']);
        $this->assertArrayHasKey('link', $result[0]);
        $this->assertArrayHasKey('county', $result[0]);
        $this->assertArrayHasKey('town', $result[0]);
        $this->assertArrayHasKey('reviews', $result[0]);
        $this->assertArrayHasKey('distance', $result[0]);
    }

    #[Test]
    public function itPassesCustomRadiusAndSort(): void
    {
        $pipeline = Mockery::mock(GetEateriesForAskSealiacSearchPipeline::class);
        $pipeline->shouldReceive('run')
            ->once()
            ->withArgs(fn (string $term, int $radius, array $filters, string $sort) => $term === 'Manchester' && $radius === 10 && $sort === 'rating')
            ->andReturn(collect());

        $this->app->instance(GetEateriesForAskSealiacSearchPipeline::class, $pipeline);

        $tool = new SearchEateriesBySearchTermTool();
        $tool->handle(new Request([
            'term' => 'Manchester',
            'radius' => 10,
            'sort' => 'rating',
            'type' => [],
            'venueTypes' => [],
            'features' => [],
        ]));
    }

    #[Test]
    public function itPassesFiltersToThePipeline(): void
    {
        $pipeline = Mockery::mock(GetEateriesForAskSealiacSearchPipeline::class);
        $pipeline->shouldReceive('run')
            ->once()
            ->withArgs(fn (string $term, int $radius, array $filters, string $sort) => $filters['categories'] === ['eatery']
                    && $filters['venueTypes'] === ['pub']
                    && $filters['features'] === ['100-gluten-free'])
            ->andReturn(collect());

        $this->app->instance(GetEateriesForAskSealiacSearchPipeline::class, $pipeline);

        $tool = new SearchEateriesBySearchTermTool();
        $tool->handle(new Request([
            'term' => 'London',
            'type' => ['eatery'],
            'venueTypes' => ['pub'],
            'features' => ['100-gluten-free'],
        ]));
    }

    #[Test]
    public function itPassesNullFiltersWhenEmpty(): void
    {
        $pipeline = Mockery::mock(GetEateriesForAskSealiacSearchPipeline::class);
        $pipeline->shouldReceive('run')
            ->once()
            ->withArgs(fn (string $term, int $radius, array $filters) => $filters['categories'] === null
                    && $filters['venueTypes'] === null
                    && $filters['features'] === null)
            ->andReturn(collect());

        $this->app->instance(GetEateriesForAskSealiacSearchPipeline::class, $pipeline);

        $tool = new SearchEateriesBySearchTermTool();
        $tool->handle(new Request([
            'term' => 'London',
            'type' => [],
            'venueTypes' => [],
            'features' => [],
        ]));
    }

    #[Test]
    public function itTracksToolUseInChatContext(): void
    {
        $pipeline = Mockery::mock(GetEateriesForAskSealiacSearchPipeline::class);
        $pipeline->shouldReceive('run')->andReturn(collect());
        $this->app->instance(GetEateriesForAskSealiacSearchPipeline::class, $pipeline);

        $tool = new SearchEateriesBySearchTermTool();
        $tool->handle(new Request([
            'term' => 'test',
            'type' => [],
            'venueTypes' => [],
            'features' => [],
        ]));

        $toolUses = ChatContext::getToolUses();

        $this->assertCount(1, $toolUses);
        $this->assertEquals('SearchEateriesBySearchTermTool', $toolUses->first()['tool']);
    }

    #[Test]
    public function itHasTheCorrectSchema(): void
    {
        $tool = new SearchEateriesBySearchTermTool();
        $schema = new \Illuminate\JsonSchema\JsonSchemaTypeFactory();

        $result = $tool->schema($schema);

        $this->assertArrayHasKey('term', $result);
        $this->assertArrayHasKey('radius', $result);
        $this->assertArrayHasKey('sort', $result);
        $this->assertArrayHasKey('venueTypes', $result);
        $this->assertArrayHasKey('type', $result);
        $this->assertArrayHasKey('features', $result);
    }
}
