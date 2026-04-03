<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools\AskSealiac;

use App\Ai\State\ChatContext;
use App\Ai\Tools\AskSealiac\GreetingTool;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GreetingToolTest extends TestCase
{
    protected function tearDown(): void
    {
        ChatContext::clear();

        parent::tearDown();
    }

    #[Test]
    public function itReturnsTheDescription(): void
    {
        $tool = new GreetingTool();

        $result = $tool->handle(new Request());

        $this->assertEquals('Gives an introductory greeting to the user', (string) $result);
    }

    #[Test]
    public function itHasAnEmptySchema(): void
    {
        $tool = new GreetingTool();
        $schema = new \Illuminate\JsonSchema\JsonSchemaTypeFactory();

        $this->assertEmpty($tool->schema($schema));
    }

    #[Test]
    public function itTracksToolUseInChatContext(): void
    {
        $tool = new GreetingTool();

        $tool->handle(new Request());

        $toolUses = ChatContext::getToolUses();

        $this->assertCount(1, $toolUses);
        $this->assertEquals('GreetingTool', $toolUses->first()['tool']);
    }
}
