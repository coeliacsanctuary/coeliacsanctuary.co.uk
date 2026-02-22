<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\AskSealiacChat;
use Exception;
use Illuminate\Console\Command;
use OpenAI\Laravel\Facades\OpenAI;

class SummariseAskSealiacChatsCommand extends Command
{
    protected $signature = 'coeliac:summarise-ask-sealiac-chats';

    public function handle(): void
    {
        AskSealiacChat::query()
            ->whereNull('summary')
            ->where('updated_at', '<', now()->subMinutes(15))
            ->with('messages')
            ->lazy()
            ->each(function (AskSealiacChat $chat): void {
                try {
                    $conversation = $chat->messages
                        ->flatMap(fn ($message) => [
                            ['role' => 'user', 'content' => $message->prompt],
                            ['role' => 'assistant', 'content' => $message->response],
                        ])
                        ->all();

                    $result = OpenAI::chat()->create([
                        'model' => 'gpt-4o-mini',
                        'messages' => [
                            ['role' => 'system', 'content' => 'Summarise the following conversation in a single concise paragraph. Focus on the main topic and intent of the user.'],
                            ...$conversation,
                        ],
                    ]);

                    $chat->updateQuietly([
                        'summary' => $result->choices[0]->message->content,
                    ]);

                    $this->info("Summarised chat {$chat->id}");
                } catch (Exception $e) {
                    $this->error("Failed to summarise chat {$chat->id}: {$e->getMessage()}");
                }
            });
    }
}
