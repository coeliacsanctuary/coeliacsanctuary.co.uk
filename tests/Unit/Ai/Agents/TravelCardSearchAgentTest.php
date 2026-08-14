<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Agents;

use App\Ai\Agents\TravelCardSearchAgent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TravelCardSearchAgentTest extends TestCase
{
    protected TravelCardSearchAgent $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agent = new TravelCardSearchAgent();
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
    public function theInstructionsMentionTravelCards(): void
    {
        $this->assertStringContainsString('travel cards', (string) $this->agent->instructions());
    }

    #[Test]
    public function itImplementsHasStructuredOutput(): void
    {
        $this->assertInstanceOf(HasStructuredOutput::class, $this->agent);
    }

    #[Test]
    public function lookupReturnsTheFirstResultFromTheAgentResponse(): void
    {
        TravelCardSearchAgent::fake([['results' => ['France'], 'explanation' => 'test']]);

        $this->assertEquals('France', $this->agent->lookup('Paris'));
    }

    #[Test]
    public function lookupReturnsNullWhenTheAgentReturnsNoResults(): void
    {
        TravelCardSearchAgent::fake([['results' => [], 'explanation' => 'no match']]);

        $this->assertNull($this->agent->lookup('xyzzy'));
    }
}
