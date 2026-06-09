<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\AskSealiac;

use App\Ai\Agents\AskSealiac;
use App\Ai\State\ChatContext;
use App\Http\Requests\Api\AskSealiac\StoreRequest;
use Generator;
use Laravel\Ai\Streaming\Events\TextDelta;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StoreController
{
    public function __invoke(StoreRequest $request): StreamedResponse
    {
        ChatContext::setChatId($request->string('chatId')->toString());

        $stream = AskSealiac::make()
            ->withMessages($request->array('messages'))
            ->stream($request->string('prompt')->toString());

        return response()->stream(function () use ($stream): Generator {
            foreach ($stream as $event) {
                if ($event instanceof TextDelta) {
                    yield $event->delta;
                }
            }
        });
    }
}
