<?php

declare(strict_types=1);

namespace App\Ai\Middleware;

use App\Ai\State\ChatContext;
use App\Models\AskSealiacChat;
use Closure;
use Illuminate\Http\Request;
use Laravel\Ai\Gateway\StepResponse;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Messages\UserMessage;
use Laravel\Ai\PendingStep;

class LogChatMiddleware
{
    public function handle(PendingStep $step, Closure $next): mixed
    {
        return $next($step)->then(function (StepResponse $response) use ($step): void {
            if ( ! $this->isFinalResponse($step, $response)) {
                return;
            }

            $chat = AskSealiacChat::query()->firstOrCreate([
                'session_id' => app(Request::class)->session()->getId(),
                'chat_id' => ChatContext::getChatId(),
            ]);

            $chat->messages()->create([
                'prompt' => collect($step->messages)->last(fn (Message $message): bool => $message instanceof UserMessage)?->content,
                'response' => $response->text,
                'tool_uses' => ChatContext::getToolUses(),
            ]);

            $chat->update(['summary' => null]);

            ChatContext::clear();
        });
    }

    protected function isFinalResponse(PendingStep $step, StepResponse $response): bool
    {
        return $response->toolCalls === [] || $step->isFinalStep;
    }
}
