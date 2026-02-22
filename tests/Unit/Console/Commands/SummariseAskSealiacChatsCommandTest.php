<?php

declare(strict_types=1);

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\SummariseAskSealiacChatsCommand;
use App\Models\AskSealiacChat;
use App\Models\AskSealiacChatMessage;
use Carbon\Carbon;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Resources\Chat;
use OpenAI\Responses\Chat\CreateResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SummariseAskSealiacChatsCommandTest extends TestCase
{
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

        OpenAI::fake([CreateResponse::fake([
            'choices' => [
                [
                    'message' => [
                        'content' => 'User asked about gluten-free restaurants in London.',
                    ],
                ],
            ],
        ])]);

        $this->artisan(SummariseAskSealiacChatsCommand::class);

        OpenAI::assertSent(Chat::class, function (string $method, array $parameters): bool {
            $this->assertEquals('create', $method);
            $this->assertEquals('gpt-4o-mini', $parameters['model']);

            return true;
        });

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

        OpenAI::fake();

        $this->artisan(SummariseAskSealiacChatsCommand::class);

        OpenAI::assertNothingSent();

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

        OpenAI::fake();

        $this->artisan(SummariseAskSealiacChatsCommand::class);

        OpenAI::assertNothingSent();
    }

    #[Test]
    public function itBuildsTheConversationFromMessages(): void
    {
        Carbon::setTestNow('2026-02-12 12:00:00');

        $chat = $this->create(AskSealiacChat::class, [
            'updated_at' => '2026-02-12 11:40:00',
        ]);

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

        OpenAI::fake([CreateResponse::fake([
            'choices' => [
                [
                    'message' => [
                        'content' => 'Summary of conversation.',
                    ],
                ],
            ],
        ])]);

        $this->artisan(SummariseAskSealiacChatsCommand::class);

        OpenAI::assertSent(Chat::class, function (string $method, array $parameters): bool {
            $messages = $parameters['messages'];

            $this->assertEquals('system', $messages[0]['role']);
            $this->assertEquals('user', $messages[1]['role']);
            $this->assertEquals('First question', $messages[1]['content']);
            $this->assertEquals('assistant', $messages[2]['role']);
            $this->assertEquals('First answer', $messages[2]['content']);
            $this->assertEquals('user', $messages[3]['role']);
            $this->assertEquals('Second question', $messages[3]['content']);
            $this->assertEquals('assistant', $messages[4]['role']);
            $this->assertEquals('Second answer', $messages[4]['content']);

            return true;
        });
    }
}
