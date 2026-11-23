<?php

declare(strict_types=1);

namespace App\Actions\EatingOut;

use App\Ai\Agents\PrepareRecommendedEatery;
use App\DataObjects\EatingOut\AiPreparedRecommendation;
use App\Models\EatingOut\EateryRecommendation;
use Laravel\Ai\Responses\StructuredAgentResponse;

class SendEateryRecommendationToAiAction
{
    public function handle(EateryRecommendation $eateryRecommendation): AiPreparedRecommendation
    {
        $prompt = view('prompts.prepare-recommended-eatery-prompt', [
            'recommendation' => $eateryRecommendation,
        ])->render();

        /** @var StructuredAgentResponse $response */
        $response = PrepareRecommendedEatery::make()->prompt($prompt);

        return AiPreparedRecommendation::fromArray($response->toArray());
    }
}
