<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools\AskSealiac;

use App\Ai\State\ChatContext;
use App\Ai\Tools\AskSealiac\GetEateryTownsTool;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use Database\Seeders\EateryScaffoldingSeeder;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetEateryTownsToolTest extends TestCase
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
    public function itReturnsTownsForAGivenCounty(): void
    {
        $county = $this->create(EateryCounty::class, ['county' => 'Dorset', 'country_id' => 1]);
        $town = $this->create(EateryTown::class, ['town' => 'Bournemouth', 'county_id' => $county->id]);
        $this->create(Eatery::class, ['town_id' => $town->id, 'county_id' => $county->id, 'country_id' => 1]);

        $otherCounty = $this->create(EateryCounty::class, ['county' => 'Devon', 'country_id' => 1]);
        $otherTown = $this->create(EateryTown::class, ['town' => 'Exeter', 'county_id' => $otherCounty->id]);
        $this->create(Eatery::class, ['town_id' => $otherTown->id, 'county_id' => $otherCounty->id, 'country_id' => 1]);

        $tool = new GetEateryTownsTool();
        $result = json_decode((string) $tool->handle(new Request(['county_id' => $county->id])), true);

        $this->assertCount(1, $result);
        $this->assertEquals($town->id, $result[0]['id']);
        $this->assertEquals('Bournemouth', $result[0]['name']);
        $this->assertArrayHasKey('link', $result[0]);
    }

    #[Test]
    public function itReturnsMultipleTowns(): void
    {
        $county = $this->create(EateryCounty::class, ['country_id' => 1]);

        $townA = $this->create(EateryTown::class, ['town' => 'Bournemouth', 'county_id' => $county->id]);
        $this->create(Eatery::class, ['town_id' => $townA->id, 'county_id' => $county->id, 'country_id' => 1]);

        $townB = $this->create(EateryTown::class, ['town' => 'Poole', 'county_id' => $county->id]);
        $this->create(Eatery::class, ['town_id' => $townB->id, 'county_id' => $county->id, 'country_id' => 1]);

        $tool = new GetEateryTownsTool();
        $result = json_decode((string) $tool->handle(new Request(['county_id' => $county->id])), true);

        $this->assertCount(2, $result);
    }

    #[Test]
    public function itReturnsEmptyWhenNoTownsForCounty(): void
    {
        $county = $this->create(EateryCounty::class, ['country_id' => 1]);

        $tool = new GetEateryTownsTool();
        $result = json_decode((string) $tool->handle(new Request(['county_id' => $county->id])), true);

        $this->assertEmpty($result);
    }

    #[Test]
    public function itTracksToolUseInChatContext(): void
    {
        $tool = new GetEateryTownsTool();
        $tool->handle(new Request(['county_id' => 1]));

        $toolUses = ChatContext::getToolUses();

        $this->assertCount(1, $toolUses);
        $this->assertEquals('GetEateryTownsTool', $toolUses->first()['tool']);
    }
}
