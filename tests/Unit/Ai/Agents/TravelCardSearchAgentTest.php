<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Agents;

use App\Ai\Agents\TravelCardSearchAgent;
use Illuminate\Support\Collection;
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

        $results = $this->agent->lookup('Paris');

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertcount(1, $results);
        $this->assertEquals('France', $results->first());
    }

    #[Test]
    public function lookupReturnsAnEmptyResultSetWhenTheAgentReturnsNoResults(): void
    {
        TravelCardSearchAgent::fake([['results' => [], 'explanation' => 'no match']]);

        $this->assertEmpty($this->agent->lookup('xyzzy'));
    }
}
