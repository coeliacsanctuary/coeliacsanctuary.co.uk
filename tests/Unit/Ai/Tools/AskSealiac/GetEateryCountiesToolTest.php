<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools\AskSealiac;

use App\Ai\State\ChatContext;
use App\Ai\Tools\AskSealiac\GetEateryCountiesTool;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use Database\Seeders\EateryScaffoldingSeeder;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetEateryCountiesToolTest extends TestCase
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
    public function itReturnsCountiesForAGivenCountry(): void
    {
        $country = $this->create(EateryCountry::class, ['country' => 'Wales']);
        $county = $this->create(EateryCounty::class, ['county' => 'Dorset', 'country_id' => $country->id]);
        $town = $this->create(EateryTown::class, ['county_id' => $county->id]);

        $this->create(Eatery::class, ['town_id' => $town->id, 'county_id' => $county->id, 'country_id' => $country->id]);

        $otherCountry = $this->create(EateryCountry::class, ['country' => 'Scotland']);
        $otherCounty = $this->create(EateryCounty::class, ['county' => 'Fife', 'country_id' => $otherCountry->id]);
        $otherTown = $this->create(EateryTown::class, ['county_id' => $otherCounty->id]);

        $this->create(Eatery::class, ['town_id' => $otherTown->id, 'county_id' => $otherCounty->id, 'country_id' => $otherCountry->id]);

        $tool = new GetEateryCountiesTool();

        $result = json_decode((string) $tool->handle(new Request(['country_id' => $country->id])), true);

        $this->assertCount(1, $result);
        $this->assertEquals($county->id, $result[0]['id']);
        $this->assertEquals('Dorset', $result[0]['county']);
        $this->assertArrayHasKey('link', $result[0]);
    }

    #[Test]
    public function itExcludesNationwideCounty(): void
    {
        $country = $this->create(EateryCountry::class, ['country' => 'Wales']);

        $county = $this->create(EateryCounty::class, ['county' => 'Dorset', 'country_id' => $country->id]);
        $town = $this->create(EateryTown::class, ['county_id' => $county->id]);
        $this->create(Eatery::class, ['town_id' => $town->id, 'county_id' => $county->id, 'country_id' => $country->id]);

        $nationwideCounty = $this->create(EateryCounty::class, ['county' => 'Nationwide', 'country_id' => $country->id]);
        $nationwideTown = $this->create(EateryTown::class, ['county_id' => $nationwideCounty->id]);
        $this->create(Eatery::class, ['town_id' => $nationwideTown->id, 'county_id' => $nationwideCounty->id, 'country_id' => $country->id]);

        $tool = new GetEateryCountiesTool();
        $result = json_decode((string) $tool->handle(new Request(['country_id' => $country->id])), true);

        $this->assertCount(1, $result);
        $this->assertEquals('Dorset', $result[0]['county']);
    }

    #[Test]
    public function itReturnsEmptyWhenNoCountiesForCountry(): void
    {
        $country = $this->create(EateryCountry::class, ['country' => 'Wales']);

        $tool = new GetEateryCountiesTool();
        $result = json_decode((string) $tool->handle(new Request(['country_id' => $country->id])), true);

        $this->assertEmpty($result);
    }

    #[Test]
    public function itTracksToolUseInChatContext(): void
    {
        $tool = new GetEateryCountiesTool();
        $tool->handle(new Request(['country_id' => 1]));

        $toolUses = ChatContext::getToolUses();

        $this->assertCount(1, $toolUses);
        $this->assertEquals('GetEateryCountiesTool', $toolUses->first()['tool']);
    }
}
