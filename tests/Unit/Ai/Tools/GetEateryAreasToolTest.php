<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools;

use App\Ai\State\ChatContext;
use App\Ai\Tools\GetEateryAreasTool;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use Database\Seeders\EateryScaffoldingSeeder;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetEateryAreasToolTest extends TestCase
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
    public function itReturnsAreasForAGivenTown(): void
    {
        $county = $this->create(EateryCounty::class, ['county' => 'London', 'country_id' => 1]);
        $town = $this->create(EateryTown::class, ['town' => 'City of Westminster', 'county_id' => $county->id]);
        $area = $this->create(EateryArea::class, ['area' => 'Leicester Square', 'town_id' => $town->id]);

        $this->create(Eatery::class, ['town_id' => $town->id, 'county_id' => $county->id, 'country_id' => 1, 'area_id' => $area->id]);

        $otherTown = $this->create(EateryTown::class, ['town' => 'Camden', 'county_id' => $county->id]);
        $otherArea = $this->create(EateryArea::class, ['area' => 'Kings Cross', 'town_id' => $otherTown->id]);

        $this->create(Eatery::class, ['town_id' => $otherTown->id, 'county_id' => $county->id, 'country_id' => 1, 'area_id' => $otherArea->id]);

        $tool = new GetEateryAreasTool();

        $result = json_decode((string) $tool->handle(new Request(['town_id' => $town->id])), true);

        $this->assertCount(1, $result);
        $this->assertEquals($area->id, $result[0]['id']);
        $this->assertEquals('Leicester Square', $result[0]['name']);
        $this->assertArrayHasKey('link', $result[0]);
    }

    #[Test]
    public function itReturnsMultipleAreas(): void
    {
        $county = $this->create(EateryCounty::class, ['county' => 'London', 'country_id' => 1]);
        $town = $this->create(EateryTown::class, ['county_id' => $county->id]);

        $areaA = $this->create(EateryArea::class, ['area' => 'Leicester Square', 'town_id' => $town->id]);
        $this->create(Eatery::class, ['town_id' => $town->id, 'county_id' => $county->id, 'country_id' => 1, 'area_id' => $areaA->id]);

        $areaB = $this->create(EateryArea::class, ['area' => 'Covent Garden', 'town_id' => $town->id]);
        $this->create(Eatery::class, ['town_id' => $town->id, 'county_id' => $county->id, 'country_id' => 1, 'area_id' => $areaB->id]);

        $tool = new GetEateryAreasTool();
        $result = json_decode((string) $tool->handle(new Request(['town_id' => $town->id])), true);

        $this->assertCount(2, $result);
    }

    #[Test]
    public function itReturnsEmptyWhenNoAreasForTown(): void
    {
        $county = $this->create(EateryCounty::class, ['county' => 'London', 'country_id' => 1]);
        $town = $this->create(EateryTown::class, ['county_id' => $county->id]);
        $this->create(Eatery::class, ['town_id' => $town->id, 'county_id' => $county->id, 'country_id' => 1]);

        $tool = new GetEateryAreasTool();
        $result = json_decode((string) $tool->handle(new Request(['town_id' => $town->id])), true);

        $this->assertEmpty($result);
    }

    #[Test]
    public function itTracksToolUseInChatContext(): void
    {
        $tool = new GetEateryAreasTool();
        $tool->handle(new Request(['town_id' => 1]));

        $toolUses = ChatContext::getToolUses();

        $this->assertCount(1, $toolUses);
        $this->assertEquals('GetEateryAreasTool', $toolUses->first()['tool']);
    }
}
