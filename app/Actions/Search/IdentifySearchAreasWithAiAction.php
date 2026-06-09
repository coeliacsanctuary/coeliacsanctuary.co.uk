<?php

declare(strict_types=1);

namespace App\Actions\Search;

use App\Ai\Agents\SearchAreasAgent;
use App\DataObjects\Search\SearchAiResponse;
use App\Models\Search\Search;
use Laravel\Ai\Responses\StructuredAgentResponse;
use Throwable;

class IdentifySearchAreasWithAiAction
{
    public function handle(Search $search): ?SearchAiResponse
    {
        if ($search->aiResponse) {
            return $search->aiResponse->toDto();
        }

        try {
            /** @var StructuredAgentResponse $response */
            $response = (new SearchAreasAgent())->prompt($search->term);

            $aiResponse = SearchAiResponse::fromResponse($response->toArray());

            $search->aiResponse()->create($aiResponse->toModel());

            return $aiResponse;
        } catch (Throwable $e) {
            return null;
        }
    }
}
