<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools;

use App\Ai\State\ChatContext;
use App\Ai\Tools\GetEateryCountriesTool;
use App\Models\EatingOut\EateryCountry;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetEateryCountriesToolTest extends TestCase
{
    protected function tearDown(): void
    {
        ChatContext::clear();

        parent::tearDown();
    }

    #[Test]
    public function itReturnsAllCountries(): void
    {
        $this->create(EateryCountry::class, ['country' => 'England']);
        $this->create(EateryCountry::class, ['country' => 'Wales']);

        $tool = new GetEateryCountriesTool();
        $result = json_decode((string) $tool->handle(new Request()), true);

        $this->assertCount(2, $result);
        $this->assertEquals('England', $result[0]['country']);
        $this->assertEquals('Wales', $result[1]['country']);
    }

    #[Test]
    public function itExcludesNationwide(): void
    {
        $this->create(EateryCountry::class, ['country' => 'England']);
        $this->create(EateryCountry::class, ['country' => 'Nationwide']);

        $tool = new GetEateryCountriesTool();
        $result = json_decode((string) $tool->handle(new Request()), true);

        $this->assertCount(1, $result);
        $this->assertEquals('England', $result[0]['country']);
    }

    #[Test]
    public function itReturnsIdAndCountryFields(): void
    {
        $country = $this->create(EateryCountry::class, ['country' => 'Scotland']);

        $tool = new GetEateryCountriesTool();
        $result = json_decode((string) $tool->handle(new Request()), true);

        $this->assertArrayHasKey('id', $result[0]);
        $this->assertArrayHasKey('country', $result[0]);
        $this->assertEquals($country->id, $result[0]['id']);
    }

    #[Test]
    public function itReturnsEmptyWhenNoCountriesExist(): void
    {
        $tool = new GetEateryCountriesTool();
        $result = json_decode((string) $tool->handle(new Request()), true);

        $this->assertEmpty($result);
    }

    #[Test]
    public function itTracksToolUseInChatContext(): void
    {
        $tool = new GetEateryCountriesTool();
        $tool->handle(new Request());

        $toolUses = ChatContext::getToolUses();

        $this->assertCount(1, $toolUses);
        $this->assertEquals('GetEateryCountriesTool', $toolUses->first()['tool']);
    }

    #[Test]
    public function itHasAnEmptySchema(): void
    {
        $tool = new GetEateryCountriesTool();
        $schema = new \Illuminate\JsonSchema\JsonSchemaTypeFactory();

        $this->assertEmpty($tool->schema($schema));
    }
}
