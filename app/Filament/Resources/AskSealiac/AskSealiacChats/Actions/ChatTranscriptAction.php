<?php

declare(strict_types=1);

namespace App\Filament\Resources\AskSealiac\AskSealiacChats\Actions;

use App\Models\AskSealiacChat;
use App\Models\AskSealiacChatMessage;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class ChatTranscriptAction
{
    public static function make(): Action
    {
        return Action::make('transcript')
            ->label('View Chat')
            ->icon(Heroicon::Eye)
            ->color('gray')
            ->modalHeading('Chat Transcript')
            ->modalWidth(Width::FourExtraLarge)
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close')
            ->modalContent(fn (AskSealiacChat $record) => view('filament.actions.ask-sealiac-chat-transcript', [
                'chat' => $record,
                'turns' => static::turns($record),
            ]));
    }

    /** @return array<int, array{prompt: string, response: string, toolUses: array<int, array{tool: string, data: array<string, mixed>}>, at: string}> */
    protected static function turns(AskSealiacChat $chat): array
    {
        return $chat->messages()
            ->oldest()
            ->get()
            ->map(fn (AskSealiacChatMessage $message): array => [
                'prompt' => nl2br(e($message->prompt)),
                'response' => static::renderResponse($message->response),
                'toolUses' => $message->tool_uses ?? [],
                'at' => $message->created_at->format('jS M Y, H:i'),
            ])
            ->all();
    }

    protected static function renderResponse(string $response): string
    {
        return Str::markdown($response, [
            'renderer' => [
                'soft_break' => '<br />',
            ],
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ]);
    }
}
