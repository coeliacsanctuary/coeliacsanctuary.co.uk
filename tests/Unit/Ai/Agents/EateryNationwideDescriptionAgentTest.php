<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Agents;

use App\Ai\Agents\EateryNationwideDescriptionAgent;
use Laravel\Ai\Contracts\HasTools;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryNationwideDescriptionAgentTest extends TestCase
{
    protected EateryNationwideDescriptionAgent $agent;

    protected function setUp(): void
    {
        parent::setUp();

        $this->agent = new EateryNationwideDescriptionAgent();
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
    public function theInstructionsTellTheAgentNotToWriteAboutLocations(): void
    {
        $this->assertStringContainsString(
            'do not write about towns, locations or tourist destinations',
            (string) $this->agent->instructions()
        );
    }

    #[Test]
    public function itDoesNotUseAnyTools(): void
    {
        $this->assertNotInstanceOf(HasTools::class, $this->agent);
    }
}
