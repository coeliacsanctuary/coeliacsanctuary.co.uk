<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Agents;

use App\Ai\Agents\MagicRoutes\HundredPercentGlutenFreeCountyContentAgent;
use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryMagicRouteRecord;
use App\Models\EatingOut\EateryTown;
use App\Pipelines\EatingOut\GetEateries\GetEateriesForMagicRoutePipeline;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Illuminate\Support\Collection;
use Laravel\Ai\Contracts\HasStructuredOutput;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class HundredPercentGlutenFreeCountyContentAgentTest extends TestCase
{
    protected EateryCounty $county;

    protected EateryTown $town;

    protected Collection $eateries;

    protected EateryMagicRouteRecord $routeRecord;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->county = $this->create(EateryCounty::class);
        $this->town = $this->create(EateryTown::class, ['county_id' => $this->county->id]);

        $this->eateries = $this->build(Eatery::class)
            ->count(2)
            ->create(['town_id' => $this->town->id, 'county_id' => $this->county->id])
            ->load('town');

        $this->routeRecord = $this->create(EateryMagicRouteRecord::class, [
            'location_type' => EateryCounty::class,
            'location_id' => $this->county->id,
        ]);
    }

    protected function makeAgent(): HundredPercentGlutenFreeCountyContentAgent
    {
        $dto = new GetEateriesPipelineData(filters: [], hydrated: $this->eateries);

        $this->mock(GetEateriesForMagicRoutePipeline::class)
            ->shouldReceive('run')
            ->shouldReceive('rawData')
            ->andReturn($dto);

        return new HundredPercentGlutenFreeCountyContentAgent($this->routeRecord);
    }

    #[Test]
    public function itThrowsWhenLocationIsNotACounty(): void
    {
        $town = $this->create(EateryTown::class);

        $record = $this->create(EateryMagicRouteRecord::class, [
            'location_type' => EateryTown::class,
            'location_id' => $town->id,
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Magic route must be a for a county');

        new HundredPercentGlutenFreeCountyContentAgent($record);
    }

    #[Test]
    public function itRendersInstructions(): void
    {
        $this->assertNotEmpty((string) $this->makeAgent()->instructions());
    }

    #[Test]
    public function theInstructionsMentionCoeliacSanctuary(): void
    {
        $this->assertStringContainsString('Coeliac Sanctuary', (string) $this->makeAgent()->instructions());
    }

    #[Test]
    public function theInstructionsMentionGlutenFree(): void
    {
        $this->assertStringContainsString('gluten free', (string) $this->makeAgent()->instructions());
    }

    #[Test]
    public function theInstructionsMentionTheCounty(): void
    {
        $this->assertStringContainsString($this->county->county, (string) $this->makeAgent()->instructions());
    }

    #[Test]
    public function itImplementsHasStructuredOutput(): void
    {
        $this->assertInstanceOf(HasStructuredOutput::class, $this->makeAgent());
    }

    #[Test]
    public function theSchemaContainsAPageIntroKey(): void
    {
        $agent = $this->makeAgent();

        $schema = $agent->schema(new JsonSchemaTypeFactory());

        $this->assertArrayHasKey('page_intro', $schema);
    }

    #[Test]
    public function theSchemaContainsAKeyForEachTown(): void
    {
        $agent = $this->makeAgent();

        $schema = $agent->schema(new JsonSchemaTypeFactory());

        $this->eateries->pluck('town.slug')->unique()->each(
            fn (string $slug) => $this->assertArrayHasKey($slug, $schema),
        );
    }
}
