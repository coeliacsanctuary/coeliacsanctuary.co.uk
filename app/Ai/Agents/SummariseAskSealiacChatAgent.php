<?php

declare(strict_types=1);

namespace App\Ai\Agents;

use App\Models\AskSealiacChat;
use App\Models\AskSealiacChatMessage;
use Laravel\Ai\Attributes\Model;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Stringable;

#[Model('gpt-4o-mini')]
class SummariseAskSealiacChatAgent implements Agent, Conversational
{
    use Promptable;

    public function __construct(protected AskSealiacChat $chat)
    {
        $this->chat->loadMissing('messages');
    }

    public function instructions(): Stringable|string
    {
        return 'Summarise the following conversation in a single concise paragraph. Focus on the main topic and intent of the user.';
    }

    /** @return Message[] */
    public function messages(): iterable
    {
        return $this->chat->messages
            ->flatMap(fn (AskSealiacChatMessage $message) => [
                new Message('user', $message->prompt),
                new Message('assistant', $message->response),
            ])
            ->all();
    }
}
