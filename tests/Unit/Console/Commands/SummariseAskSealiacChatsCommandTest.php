<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Commands;

use App\Ai\Agents\SummariseAskSealiacChatAgent;
use App\Console\Commands\SummariseAskSealiacChatsCommand;
use App\Models\AskSealiacChat;
use App\Models\AskSealiacChatMessage;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SummariseAskSealiacChatsCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        SummariseAskSealiacChatAgent::fake(['User asked about gluten-free restaurants in London.']);
    }

    #[Test]
    public function itSummarisesChatsWithNullSummaryOlderThan15Minutes(): void
    {
        Carbon::setTestNow('2026-02-12 12:00:00');

        $chat = $this->create(AskSealiacChat::class, [
            'updated_at' => '2026-02-12 11:40:00',
        ]);

        $this->create(AskSealiacChatMessage::class, [
            'ask_sealiac_chat_id' => $chat->id,
            'prompt' => 'Where can I eat in London?',
            'response' => 'Here are some places...',
        ]);

        $this->artisan(SummariseAskSealiacChatsCommand::class);

        $this->assertEquals(
            'User asked about gluten-free restaurants in London.',
            $chat->refresh()->summary,
        );
    }

    #[Test]
    public function itDoesNotSummariseChatsUpdatedWithinTheLast15Minutes(): void
    {
        Carbon::setTestNow('2026-02-12 12:00:00');

        $chat = $this->create(AskSealiacChat::class, [
            'updated_at' => '2026-02-12 11:50:00',
        ]);

        $this->create(AskSealiacChatMessage::class, [
            'ask_sealiac_chat_id' => $chat->id,
        ]);

        $this->artisan(SummariseAskSealiacChatsCommand::class);

        SummariseAskSealiacChatAgent::assertNeverPrompted();

        $this->assertNull($chat->refresh()->summary);
    }

    #[Test]
    public function itDoesNotSummariseChatsWithAnExistingSummary(): void
    {
        Carbon::setTestNow('2026-02-12 12:00:00');

        $chat = $this->build(AskSealiacChat::class)->withSummary()->create([
            'updated_at' => '2026-02-12 11:40:00',
        ]);

        $this->create(AskSealiacChatMessage::class, [
            'ask_sealiac_chat_id' => $chat->id,
        ]);

        $this->artisan(SummariseAskSealiacChatsCommand::class);

        SummariseAskSealiacChatAgent::assertNeverPrompted();
    }

    #[Test]
    public function itPromptsTheAgentForEachEligibleChat(): void
    {
        Carbon::setTestNow('2026-02-12 12:00:00');

        $chatOne = $this->create(AskSealiacChat::class, ['updated_at' => '2026-02-12 11:40:00']);
        $this->create(AskSealiacChatMessage::class, ['ask_sealiac_chat_id' => $chatOne->id]);

        $chatTwo = $this->create(AskSealiacChat::class, ['updated_at' => '2026-02-12 11:40:00']);
        $this->create(AskSealiacChatMessage::class, ['ask_sealiac_chat_id' => $chatTwo->id]);

        $this->artisan(SummariseAskSealiacChatsCommand::class);

        SummariseAskSealiacChatAgent::assertPrompted('Please summarise the above conversation.');
    }
}
