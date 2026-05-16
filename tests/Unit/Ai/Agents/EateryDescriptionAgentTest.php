<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Agents;

use App\Ai\Agents\EateryDescriptionAgent;
use App\Models\EatingOut\Eatery;
use Database\Seeders\EateryScaffoldingSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryDescriptionAgentTest extends TestCase
{
    protected function makeAgent(): EateryDescriptionAgent
    {
        $this->seed(EateryScaffoldingSeeder::class);

        return new EateryDescriptionAgent($this->create(Eatery::class));
    }

    #[Test]
    public function itReturnsInstructions(): void
    {
        $this->assertNotEmpty((string) $this->makeAgent()->instructions());
    }

    #[Test]
    public function theInstructionsMentionGlutenFree(): void
    {
        $this->assertStringContainsString('gluten free', (string) $this->makeAgent()->instructions());
    }

    #[Test]
    public function theInstructionsMentionCoeliac(): void
    {
        $this->assertStringContainsString('coeliac', (string) $this->makeAgent()->instructions());
    }

    #[Test]
    public function theInstructionsIncludeTheEateryName(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);

        $eatery = $this->create(Eatery::class, ['name' => 'My Gluten Free Café']);
        $agent = new EateryDescriptionAgent($eatery);

        $this->assertStringContainsString('My Gluten Free Café', (string) $agent->instructions());
    }

    #[Test]
    public function theInstructionsIncludeTheTownName(): void
    {
        $this->seed(EateryScaffoldingSeeder::class);

        $eatery = $this->create(Eatery::class);
        $agent = new EateryDescriptionAgent($eatery);

        $this->assertStringContainsString($eatery->town->town, (string) $agent->instructions());
    }

    #[Test]
    public function theInstructionsMentionTheCharacterLimit(): void
    {
        $this->assertStringContainsString('330', (string) $this->makeAgent()->instructions());
    }
}
