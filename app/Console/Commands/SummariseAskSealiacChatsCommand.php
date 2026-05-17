<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Ai\Agents\SummariseAskSealiacChatAgent;
use App\Models\AskSealiacChat;
use Exception;
use Illuminate\Console\Command;

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
                    $response = (new SummariseAskSealiacChatAgent($chat))->prompt('Please summarise the above conversation.');

                    $chat->updateQuietly([
                        'summary' => $response->text,
                    ]);

                    $this->info("Summarised chat {$chat->id}");
                } catch (Exception $e) {
                    $this->error("Failed to summarise chat {$chat->id}: {$e->getMessage()}");
                }
            });
    }
}
