<?php

declare(strict_types=1);

namespace App\Ai\Middleware;

use App\Ai\State\ChatContext;
use App\Models\AskSealiacChat;
use Closure;
use Illuminate\Http\Request;
use Laravel\Ai\Prompts\AgentPrompt;
use Laravel\Ai\Responses\AgentResponse;

class LogChatMiddleware
{
    public function handle(AgentPrompt $prompt, Closure $next): mixed
    {
        $chat = AskSealiacChat::query()->firstOrCreate([
            'session_id' => app(Request::class)->session()->getId(),
            'chat_id' => ChatContext::getChatId(),
        ]);

        return $next($prompt)->then(function (AgentResponse $response) use ($prompt, $chat): void {
            $chat->messages()->create([
                'prompt' => $prompt->prompt,
                'response' => $response->text,
                'tool_uses' => ChatContext::getToolUses(),
            ]);

            $chat->update(['summary' => null]);

            ChatContext::clear();
        });
    }
}
