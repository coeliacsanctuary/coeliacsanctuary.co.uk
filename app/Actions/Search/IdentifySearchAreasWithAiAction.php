<?php

declare(strict_types=1);

namespace App\Actions\Search;

use App\DataObjects\Search\SearchAiResponse;
use App\Models\Search\Search;
use OpenAI\Laravel\Facades\OpenAI;
use Throwable;

class IdentifySearchAreasWithAiAction
{
    public function handle(Search $search): ?SearchAiResponse
    {
        if ($search->aiResponse) {
            return $search->aiResponse->toDto();
        }

        try {
            $result = OpenAI::chat()->create([
                'model' => 'gpt-3.5-turbo-1106',
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => view('prompts.search', ['searchTerm' => $search->term])->render(),
                    ],
                ],
            ]);

            /** @var string $response */
            $response = $result->choices[0]->message->content;

            if ( ! json_validate($response)) {
                return null;
            }

            /** @var array $json */
            $json = json_decode($response, true);

            $aiResponse = SearchAiResponse::fromResponse($json);

            $search->aiResponse()->create($aiResponse->toModel());

            return $aiResponse;
        } catch (Throwable $e) {
            return null;
        }
    }
}
