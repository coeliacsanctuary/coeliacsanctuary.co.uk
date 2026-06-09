<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools\AskSealiac;

use App\Ai\State\ChatContext;
use App\Ai\Tools\AskSealiac\WhatAreTravelCardsTool;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WhatAreTravelCardsToolTest extends TestCase
{
    protected function tearDown(): void
    {
        ChatContext::clear();

        parent::tearDown();
    }

    #[Test]
    public function itReturnsTheDescriptionOfTravelCards(): void
    {
        $tool = new WhatAreTravelCardsTool();

        $result = (string) $tool->handle(new Request());

        $this->assertStringContainsString('Standard travel cards', $result);
        $this->assertStringContainsString('Coeliac+ travel cards', $result);
        $this->assertStringContainsString('Translated by native speakers', $result);
    }

    #[Test]
    public function itHasAnEmptySchema(): void
    {
        $tool = new WhatAreTravelCardsTool();
        $schema = new \Illuminate\JsonSchema\JsonSchemaTypeFactory();

        $this->assertEmpty($tool->schema($schema));
    }

    #[Test]
    public function itTracksToolUseInChatContext(): void
    {
        $tool = new WhatAreTravelCardsTool();

        $tool->handle(new Request());

        $toolUses = ChatContext::getToolUses();

        $this->assertCount(1, $toolUses);
        $this->assertEquals('WhatAreTravelCardsTool', $toolUses->first()['tool']);
    }
}
