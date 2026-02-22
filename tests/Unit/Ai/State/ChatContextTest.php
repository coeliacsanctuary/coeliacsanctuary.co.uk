<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\State;

use App\Ai\State\ChatContext;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ChatContextTest extends TestCase
{
    protected function tearDown(): void
    {
        ChatContext::clear();

        parent::tearDown();
    }

    #[Test]
    public function itCanSetAndGetTheChatId(): void
    {
        ChatContext::clear();
        $this->assertNull(ChatContext::getChatId());

        ChatContext::setChatId('chat-123');

        $this->assertEquals('chat-123', ChatContext::getChatId());
    }

    #[Test]
    public function itReturnsAnEmptyCollectionWhenNoToolUsesHaveBeenAdded(): void
    {
        $toolUses = ChatContext::getToolUses();

        $this->assertCount(0, $toolUses);
    }

    #[Test]
    public function itCanAddAndRetrieveToolUses(): void
    {
        ChatContext::addToolUse('SearchRecipesTool', ['query' => 'pasta']);

        $toolUses = ChatContext::getToolUses();

        $this->assertCount(1, $toolUses);
        $this->assertEquals('SearchRecipesTool', $toolUses->first()['tool']);
        $this->assertEquals(['query' => 'pasta'], $toolUses->first()['data']);
    }

    #[Test]
    public function itCanAddMultipleToolUses(): void
    {
        ChatContext::addToolUse('SearchRecipesTool', ['query' => 'pasta']);
        ChatContext::addToolUse('ViewRecipeTool', ['id' => 1]);

        $toolUses = ChatContext::getToolUses();

        $this->assertCount(2, $toolUses);
        $this->assertEquals('SearchRecipesTool', $toolUses->first()['tool']);
        $this->assertEquals('ViewRecipeTool', $toolUses->last()['tool']);
    }

    #[Test]
    public function itCanAddToolUsesWithEmptyData(): void
    {
        ChatContext::addToolUse('GreetingTool');

        $toolUses = ChatContext::getToolUses();

        $this->assertCount(1, $toolUses);
        $this->assertEquals('GreetingTool', $toolUses->first()['tool']);
        $this->assertEquals([], $toolUses->first()['data']);
    }

    #[Test]
    public function itClearsAllState(): void
    {
        ChatContext::setChatId('chat-123');
        ChatContext::addToolUse('SearchRecipesTool', ['query' => 'pasta']);

        ChatContext::clear();

        $this->assertNull(ChatContext::getChatId());
        $this->assertCount(0, ChatContext::getToolUses());
    }
}
