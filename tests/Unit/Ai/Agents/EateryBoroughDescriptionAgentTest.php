<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Agents;

use App\Ai\Agents\EateryBoroughDescriptionAgent;
use App\Ai\Tools\FindLinkForLondonAreaTool;
use App\Ai\Tools\ListPopularEateriesInTown;
use Laravel\Ai\Contracts\Tool;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryBoroughDescriptionAgentTest extends TestCase
{
    protected EateryBoroughDescriptionAgent $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agent = new EateryBoroughDescriptionAgent();
    }

    #[Test]
    public function itReturnsInstructions(): void
    {
        $this->assertNotEmpty((string) $this->agent->instructions());
    }

    #[Test]
    public function theInstructionsAreRenderedFromTheBladeView(): void
    {
        $this->assertStringContainsString(
            'generate SEO friendly page introduction',
            (string) $this->agent->instructions()
        );
    }

    #[Test]
    public function theInstructionsMentionTheBorough(): void
    {
        $this->assertStringContainsString('London Borough', (string) $this->agent->instructions());
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

        $this->assertEquals([FindLinkForLondonAreaTool::class, ListPopularEateriesInTown::class], $toolClasses);
    }
}
