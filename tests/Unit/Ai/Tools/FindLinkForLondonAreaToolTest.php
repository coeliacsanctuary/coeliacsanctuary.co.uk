<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools;

use App\Ai\Tools\FindLinkForLondonAreaTool;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\JsonSchema\JsonSchemaTypeFactory;
use Illuminate\Support\Facades\Queue;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FindLinkForLondonAreaToolTest extends TestCase
{
    protected EateryCounty $county;

    protected EateryTown $borough;

    protected function setUp(): void
    {
        parent::setUp();

        Queue::fake();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->county = $this->create(EateryCounty::class, ['county' => 'London']);
        $this->borough = $this->create(EateryTown::class, ['county_id' => $this->county->id, 'town' => 'Hackney']);
    }

    protected function createArea(string $name, bool $withLiveEatery = true): EateryArea
    {
        $area = $this->create(EateryArea::class, ['town_id' => $this->borough->id, 'area' => $name]);

        $this->create(Eatery::class, [
            'county_id' => $this->county->id,
            'town_id' => $this->borough->id,
            'area_id' => $area->id,
            'live' => $withLiveEatery,
        ]);

        return $area;
    }

    protected function handleTool(string $area): string
    {
        return (string) (new FindLinkForLondonAreaTool())->handle(new Request(['area' => $area]));
    }

    #[Test]
    public function itReturnsTheAreaAbsoluteLinkWhenFound(): void
    {
        $area = $this->createArea('Shoreditch');

        $this->assertEquals($area->absoluteLink(), $this->handleTool('Shoreditch'));
    }

    #[Test]
    public function itMatchesOnAPartialAreaName(): void
    {
        $area = $this->createArea('Shoreditch High Street');

        $this->assertEquals($area->absoluteLink(), $this->handleTool('Shoreditch'));
    }

    #[Test]
    public function itReturnsNotFoundWhenTheAreaDoesNotExist(): void
    {
        $this->assertEquals('- area not found -', $this->handleTool('Nonexistent Area'));
    }

    #[Test]
    public function itDoesntReturnAnAreaWithNoLiveEateries(): void
    {
        $this->createArea('Dalston', withLiveEatery: false);

        $this->assertEquals('- area not found -', $this->handleTool('Dalston'));
    }

    #[Test]
    public function itHasTheCorrectSchema(): void
    {
        $schema = (new FindLinkForLondonAreaTool())->schema(new JsonSchemaTypeFactory());

        $this->assertArrayHasKey('area', $schema);
    }
}
