<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Agents;

use App\Ai\Agents\EateryCountryDescriptionAgent;
use App\Ai\Tools\FindLinkForCountyTool;
use App\Ai\Tools\FindLinkForTownTool;
use Laravel\Ai\Contracts\Tool;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryCountryDescriptionAgentTest extends TestCase
{
    protected EateryCountryDescriptionAgent $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agent = new EateryCountryDescriptionAgent();
    }

    #[Test]
    public function itReturnsInstructions(): void
    {
        $this->assertNotEmpty((string) $this->agent->instructions());
    }

    #[Test]
    public function theInstructionsMentionGlutenFree(): void
    {
        $this->assertStringContainsString('gluten free', (string) $this->agent->instructions());
    }

    #[Test]
    public function theInstructionsMentionCoeliac(): void
    {
        $this->assertStringContainsString('coeliac', (string) $this->agent->instructions());
    }

    #[Test]
    public function itReturnsTheExpectedTools(): void
    {
        $tools = collect($this->agent->tools());

        $this->assertCount(2, $tools);
        $tools->each(fn ($tool) => $this->assertInstanceOf(Tool::class, $tool));

        $toolClasses = $tools->map(fn ($tool) => $tool::class)->values()->all();

        $this->assertEquals([FindLinkForCountyTool::class, FindLinkForTownTool::class], $toolClasses);
    }
}
