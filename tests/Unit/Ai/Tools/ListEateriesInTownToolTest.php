<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools;

use App\Ai\State\ChatContext;
use App\Ai\Tools\ListEateriesInTownTool;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Pipelines\EatingOut\GetEateries\GetEateriesInTownForAskSealiacPipeline;
use Database\Seeders\EateryScaffoldingSeeder;
use Laravel\Ai\Tools\Request;
use Mockery;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ListEateriesInTownToolTest extends TestCase
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

    protected function createTownWithEatery(): EateryTown
    {
        $county = $this->create(EateryCounty::class, ['country_id' => 1]);
        $town = $this->create(EateryTown::class, ['county_id' => $county->id]);
        $this->create(Eatery::class, ['town_id' => $town->id, 'county_id' => $county->id, 'country_id' => 1]);

        return $town;
    }

    #[Test]
    public function itCallsThePipelineWithCorrectArguments(): void
    {
        $town = $this->createTownWithEatery();

        $pipeline = Mockery::mock(GetEateriesInTownForAskSealiacPipeline::class);
        $pipeline->shouldReceive('run')
            ->once()
            ->withArgs(fn (EateryTown $argTown, array $filters, string $sort) => $argTown->id === $town->id && $sort === 'alphabetical')
            ->andReturn(collect());

        $this->app->instance(GetEateriesInTownForAskSealiacPipeline::class, $pipeline);

        $tool = new ListEateriesInTownTool();
        $result = json_decode((string) $tool->handle(new Request([
            'town_id' => $town->id,
            'sort' => 'alphabetical',
            'type' => [],
            'venueTypes' => [],
            'features' => [],
        ])), true);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    #[Test]
    public function itMapsEateryDataCorrectly(): void
    {
        $county = $this->create(EateryCounty::class, ['country_id' => 1]);
        $town = $this->create(EateryTown::class, ['county_id' => $county->id]);

        $eatery = $this->create(Eatery::class, [
            'name' => 'Test Restaurant',
            'town_id' => $town->id,
            'county_id' => $county->id,
            'country_id' => 1,
        ]);

        $eatery->load(['county', 'town', 'area', 'venueType', 'type', 'cuisine', 'restaurants', 'features', 'reviews', 'adminReview', 'openingTimes']);

        $pipeline = Mockery::mock(GetEateriesInTownForAskSealiacPipeline::class);
        $pipeline->shouldReceive('run')->andReturn(collect([$eatery]));
        $this->app->instance(GetEateriesInTownForAskSealiacPipeline::class, $pipeline);

        $tool = new ListEateriesInTownTool();
        $result = json_decode((string) $tool->handle(new Request([
            'town_id' => $town->id,
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
        $this->assertArrayHasKey('number', $result[0]['reviews']);
        $this->assertArrayHasKey('average', $result[0]['reviews']);
        $this->assertArrayHasKey('features', $result[0]);
        $this->assertArrayHasKey('is_fully_gf', $result[0]);
    }

    #[Test]
    public function itPassesFiltersToThePipeline(): void
    {
        $town = $this->createTownWithEatery();

        $pipeline = Mockery::mock(GetEateriesInTownForAskSealiacPipeline::class);
        $pipeline->shouldReceive('run')
            ->once()
            ->withArgs(fn (EateryTown $argTown, array $filters, string $sort) => $filters['categories'] === ['eatery']
                    && $filters['venueTypes'] === ['pub']
                    && $filters['features'] === ['gluten-free-menu']
                    && $sort === 'rating')
            ->andReturn(collect());

        $this->app->instance(GetEateriesInTownForAskSealiacPipeline::class, $pipeline);

        $tool = new ListEateriesInTownTool();
        $tool->handle(new Request([
            'town_id' => $town->id,
            'sort' => 'rating',
            'type' => ['eatery'],
            'venueTypes' => ['pub'],
            'features' => ['gluten-free-menu'],
        ]));
    }

    #[Test]
    public function itPassesNullFiltersWhenEmpty(): void
    {
        $town = $this->createTownWithEatery();

        $pipeline = Mockery::mock(GetEateriesInTownForAskSealiacPipeline::class);
        $pipeline->shouldReceive('run')
            ->once()
            ->withArgs(fn (EateryTown $argTown, array $filters) => $filters['categories'] === null
                    && $filters['venueTypes'] === null
                    && $filters['features'] === null)
            ->andReturn(collect());

        $this->app->instance(GetEateriesInTownForAskSealiacPipeline::class, $pipeline);

        $tool = new ListEateriesInTownTool();
        $tool->handle(new Request([
            'town_id' => $town->id,
            'type' => [],
            'venueTypes' => [],
            'features' => [],
        ]));
    }

    #[Test]
    public function itTracksToolUseInChatContext(): void
    {
        $town = $this->createTownWithEatery();

        $pipeline = Mockery::mock(GetEateriesInTownForAskSealiacPipeline::class);
        $pipeline->shouldReceive('run')->andReturn(collect());
        $this->app->instance(GetEateriesInTownForAskSealiacPipeline::class, $pipeline);

        $tool = new ListEateriesInTownTool();
        $tool->handle(new Request([
            'town_id' => $town->id,
            'type' => [],
            'venueTypes' => [],
            'features' => [],
        ]));

        $toolUses = ChatContext::getToolUses();

        $this->assertCount(1, $toolUses);
        $this->assertEquals('ListEateriesInTownTool', $toolUses->first()['tool']);
    }

    #[Test]
    public function itHasTheCorrectSchema(): void
    {
        $tool = new ListEateriesInTownTool();
        $schema = new \Illuminate\JsonSchema\JsonSchemaTypeFactory();

        $result = $tool->schema($schema);

        $this->assertArrayHasKey('town_id', $result);
        $this->assertArrayHasKey('sort', $result);
        $this->assertArrayHasKey('venueTypes', $result);
        $this->assertArrayHasKey('type', $result);
        $this->assertArrayHasKey('features', $result);
    }
}
