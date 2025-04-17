<?php

declare(strict_types=1);

namespace App\Actions\Shop\TravelCardSearch;

use App\Support\Ai\Prompts\TravelCardLookupPrompt;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use OpenAI\Laravel\Facades\OpenAI;
use Throwable;

class TravelCardSearchAiLookup
{
    public function handle(string $searchTerm): ?Collection
    {
        try {
            $result = OpenAI::chat()->create([
                'model' => 'gpt-3.5-turbo-1106',
                'messages' => [
                    ['role' => 'system', 'content' => TravelCardLookupPrompt::get($searchTerm)],
                ],
            ]);

            /** @var string $response */
            $response = $result->choices[0]->message->content;

            if ( ! json_validate($response)) {
                throw new Exception('not valid json');
            }

            /** @var array $json */
            $json = json_decode($response, true);

            $result = Arr::get($json, 'results.0');

            if ($result) {
                return app(SearchTravelCardCountyOrLanguageAction::class)->handle($result);
            }
        } catch (Throwable $e) {
            //
        }

        return collect();
    }
}
