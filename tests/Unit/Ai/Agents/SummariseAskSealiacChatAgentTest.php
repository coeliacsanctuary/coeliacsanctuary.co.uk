<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Agents;

use App\Ai\Agents\SummariseAskSealiacChatAgent;
use App\Models\AskSealiacChat;
use App\Models\AskSealiacChatMessage;
use Laravel\Ai\Messages\Message;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SummariseAskSealiacChatAgentTest extends TestCase
{
    protected function makeAgent(AskSealiacChat $chat): SummariseAskSealiacChatAgent
    {
        return new SummariseAskSealiacChatAgent($chat);
    }

    #[Test]
    public function itReturnsTheCorrectInstructions(): void
    {
        $chat = $this->create(AskSealiacChat::class);

        $instructions = (string) $this->makeAgent($chat)->instructions();

        $this->assertStringContainsString('single concise paragraph', $instructions);
        $this->assertStringContainsString('main topic and intent of the user', $instructions);
    }

    #[Test]
    public function itMapsMessagesToUserAndAssistantTurns(): void
    {
        $chat = $this->create(AskSealiacChat::class);

        $this->create(AskSealiacChatMessage::class, [
            'ask_sealiac_chat_id' => $chat->id,
            'prompt' => 'First question',
            'response' => 'First answer',
        ]);

        $this->create(AskSealiacChatMessage::class, [
            'ask_sealiac_chat_id' => $chat->id,
            'prompt' => 'Second question',
            'response' => 'Second answer',
        ]);

        $messages = $this->makeAgent($chat)->messages();

        $this->assertCount(4, $messages);

        $this->assertInstanceOf(Message::class, $messages[0]);
        $this->assertEquals('user', $messages[0]->role->value);
        $this->assertEquals('First question', $messages[0]->content);

        $this->assertInstanceOf(Message::class, $messages[1]);
        $this->assertEquals('assistant', $messages[1]->role->value);
        $this->assertEquals('First answer', $messages[1]->content);

        $this->assertInstanceOf(Message::class, $messages[2]);
        $this->assertEquals('user', $messages[2]->role->value);
        $this->assertEquals('Second question', $messages[2]->content);

        $this->assertInstanceOf(Message::class, $messages[3]);
        $this->assertEquals('assistant', $messages[3]->role->value);
        $this->assertEquals('Second answer', $messages[3]->content);
    }

    #[Test]
    public function itReturnsEmptyMessagesWhenChatHasNoMessages(): void
    {
        $chat = $this->create(AskSealiacChat::class);

        $messages = $this->makeAgent($chat)->messages();

        $this->assertCount(0, $messages);
    }
}
