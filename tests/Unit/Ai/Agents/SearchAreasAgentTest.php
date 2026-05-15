<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Agents;

use App\Ai\Agents\SearchAreasAgent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SearchAreasAgentTest extends TestCase
{
    protected SearchAreasAgent $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agent = new SearchAreasAgent();
    }

    #[Test]
    public function itRendersInstructions(): void
    {
        $this->assertNotEmpty((string) $this->agent->instructions());
    }

    #[Test]
    public function theInstructionsMentionCoeliacSanctuary(): void
    {
        $this->assertStringContainsString('Coeliac Sanctuary', (string) $this->agent->instructions());
    }

    #[Test]
    public function theInstructionsMentionTheScoringAreas(): void
    {
        $instructions = (string) $this->agent->instructions();

        $this->assertStringContainsString('shop', $instructions);
        $this->assertStringContainsString('eating-out', $instructions);
        $this->assertStringContainsString('blogs', $instructions);
        $this->assertStringContainsString('recipes', $instructions);
    }

    #[Test]
    public function theInstructionsDoNotContainAJsonFormatInstruction(): void
    {
        $this->assertStringNotContainsString('JSON object', (string) $this->agent->instructions());
    }

    #[Test]
    public function itImplementsHasStructuredOutput(): void
    {
        $this->assertInstanceOf(HasStructuredOutput::class, $this->agent);
    }
}
