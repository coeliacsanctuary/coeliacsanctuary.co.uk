<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut\MagicRouting;

use App\Actions\EatingOut\MagicRouting\GenerateNewMagicRouteAction;
use App\Ai\Agents\MagicRoutes\HundredPercentGlutenFreeMagicRouteContentAgent;
use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\Enums\EatingOut\EateryMagicRouteType;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryMagicRouteRecord;
use App\Models\EatingOut\EateryTown;
use App\Pipelines\EatingOut\GetEateries\GetEateriesForMagicRoutePipeline;
use App\Services\EatingOut\Collection\Builder\ValueObjects\Where;
use Database\Seeders\EateryScaffoldingSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GenerateNewMagicRouteActionTest extends TestCase
{
    protected EateryCounty $county;

    protected EateryTown $town;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->county = $this->create(EateryCounty::class);
        $this->town = $this->create(EateryTown::class, ['county_id' => $this->county->id]);
        $this->create(Eatery::class, ['town_id' => $this->town->id, 'county_id' => $this->county->id]);

        $this->mock(GetEateriesForMagicRoutePipeline::class)
            ->shouldReceive('run')
            ->shouldReceive('rawData')
            ->andReturn(new GetEateriesPipelineData(filters: [], hydrated: collect()));

        HundredPercentGlutenFreeMagicRouteContentAgent::fake([
            ['page_intro' => 'Test intro', 'meta_description' => 'Test meta', 'meta_keywords' => ['gluten-free']],
        ]);
    }

    #[Test]
    public function itReturnsAnEateryMagicRouteRecord(): void
    {
        $result = $this->callAction(GenerateNewMagicRouteAction::class, EateryMagicRouteType::HundredPercentGlutenFree, $this->county);

        $this->assertInstanceOf(EateryMagicRouteRecord::class, $result);
    }

    #[Test]
    public function itPersistsTheRecordToTheDatabase(): void
    {
        $this->assertDatabaseEmpty(EateryMagicRouteRecord::class);

        $this->callAction(GenerateNewMagicRouteAction::class, EateryMagicRouteType::HundredPercentGlutenFree, $this->county);

        $this->assertDatabaseCount(EateryMagicRouteRecord::class, 1);
    }

    #[Test]
    public function itSetsTheResolverTypeOnTheRecord(): void
    {
        $this->callAction(GenerateNewMagicRouteAction::class, EateryMagicRouteType::HundredPercentGlutenFree, $this->county);

        $record = EateryMagicRouteRecord::query()->first();

        $this->assertSame(EateryMagicRouteType::HundredPercentGlutenFree, $record->resolver_type);
    }

    #[Test]
    public function itSetsTheRawLocationAsTheLocationSlug(): void
    {
        $this->callAction(GenerateNewMagicRouteAction::class, EateryMagicRouteType::HundredPercentGlutenFree, $this->county);

        $record = EateryMagicRouteRecord::query()->first();

        $this->assertSame($this->county->slug, $record->raw_location);
    }

    #[Test]
    public function itSetsTheLocationTypeAsTheLocationClass(): void
    {
        $this->callAction(GenerateNewMagicRouteAction::class, EateryMagicRouteType::HundredPercentGlutenFree, $this->county);

        $record = EateryMagicRouteRecord::query()->first();

        $this->assertSame(EateryCounty::class, $record->location_type);
    }

    #[Test]
    public function itSetsTheLocationIdFromTheLocation(): void
    {
        $this->callAction(GenerateNewMagicRouteAction::class, EateryMagicRouteType::HundredPercentGlutenFree, $this->county);

        $record = EateryMagicRouteRecord::query()->first();

        $this->assertSame($this->county->id, $record->location_id);
    }

    #[Test]
    public function itAddsACountyIdWhereClauseWhenTheLocationIsACounty(): void
    {
        $this->callAction(GenerateNewMagicRouteAction::class, EateryMagicRouteType::HundredPercentGlutenFree, $this->county);

        $record = EateryMagicRouteRecord::query()->first();

        $this->assertTrue(
            $record->builder_config->getWheres()->contains(
                fn (Where $where) => $where->jsonSerialize() === ['[parent].county_id', '=', $this->county->id, 'and']
            )
        );
    }

    #[Test]
    public function itAddsATownIdWhereClauseWhenTheLocationIsATown(): void
    {
        $this->callAction(GenerateNewMagicRouteAction::class, EateryMagicRouteType::HundredPercentGlutenFree, $this->town);

        $record = EateryMagicRouteRecord::query()->first();

        $this->assertTrue(
            $record->builder_config->getWheres()->contains(
                fn (Where $where) => $where->jsonSerialize() === ['[parent].town_id', '=', $this->town->id, 'and']
            )
        );
    }

    #[Test]
    public function itAppliesTheEnumBuilderConfigurationToTheBuilderConfig(): void
    {
        $this->callAction(GenerateNewMagicRouteAction::class, EateryMagicRouteType::HundredPercentGlutenFree, $this->county);

        $record = EateryMagicRouteRecord::query()->first();
        $config = $record->builder_config;

        $this->assertTrue($config->getJoins()->isNotEmpty());

        $this->assertTrue(
            $config->getWheres()->contains(
                fn (Where $where) => $where->jsonSerialize()[0] === 'wheretoeat_assigned_features.feature_id'
            )
        );
    }

    #[Test]
    public function itSetsTheBodyFromTheAgentResponse(): void
    {
        $this->callAction(GenerateNewMagicRouteAction::class, EateryMagicRouteType::HundredPercentGlutenFree, $this->county);

        $record = EateryMagicRouteRecord::query()->first();

        $this->assertSame(
            ['page_intro' => 'Test intro', 'meta_description' => 'Test meta', 'meta_keywords' => ['gluten-free']],
            $record->body
        );
    }

    #[Test]
    public function itPromptsTheAgentWithGenerateTheContent(): void
    {
        $this->callAction(GenerateNewMagicRouteAction::class, EateryMagicRouteType::HundredPercentGlutenFree, $this->county);

        HundredPercentGlutenFreeMagicRouteContentAgent::assertPrompted('Generate the content');
    }
}
