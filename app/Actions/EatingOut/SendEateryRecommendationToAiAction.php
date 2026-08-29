<?php

declare(strict_types=1);

namespace App\Actions\EatingOut;

use App\Ai\Agents\PrepareRecommendedEatery;
use App\DataObjects\EatingOut\AiPreparedRecommendation;
use App\Models\EatingOut\EateryRecommendation;

class SendEateryRecommendationToAiAction
{
    public function handle(EateryRecommendation $eateryRecommendation): AiPreparedRecommendation
    {
        $prompt = view('prompts.prepare-recommended-eatery-prompt', [
            'recommendation' => $eateryRecommendation,
        ])->render();

        $info = PrepareRecommendedEatery::make()->prompt($prompt);

        $aiInfo = json_decode($info->text, true);

        return AiPreparedRecommendation::fromArray($aiInfo);
    }
}
