<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Agents;

use App\Ai\Agents\EateryAreaDescriptionAgent;
use App\Ai\Tools\FindLinkForLondonAreaTool;
use App\Ai\Tools\ListPopularEateriesInArea;
use App\Ai\Tools\ListPopularEateriesInTown;
use Laravel\Ai\Contracts\Tool;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryAreaDescriptionAgentTest extends TestCase
{
    protected EateryAreaDescriptionAgent $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agent = new EateryAreaDescriptionAgent();
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
        $this->assertStringContainsString('Area', (string) $this->agent->instructions());
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

        $this->assertCount(1, $tools);
        $tools->each(fn ($tool) => $this->assertInstanceOf(Tool::class, $tool));

        $toolClasses = $tools->map(fn ($tool) => $tool::class)->values()->all();

        $this->assertEquals([ListPopularEateriesInArea::class], $toolClasses);
    }
}
