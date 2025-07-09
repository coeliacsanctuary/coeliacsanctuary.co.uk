<?php

declare(strict_types=1);

namespace App\Actions\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Models\EatingOut\SealiacOverview;
use App\Support\Ai\Prompts\EatingOutSealiacOverviewPrompt;
use Exception;
use OpenAI\Laravel\Facades\OpenAI;

class GetSealiacEateryOverviewAction
{
    public function handle(Eatery $eatery, ?NationwideBranch $branch = null): string
    {
        if ($branch?->sealiacOverview) {
            return $branch->sealiacOverview->overview;
        }

        if ($eatery->sealiacOverview) {
            return $eatery->sealiacOverview->overview;
        }

        $reviewCheck = ($branch ?: $eatery)->reviews()
            ->where('approved', true)
            ->whereNot('review', '')
            ->count();

        if ($reviewCheck === 0) {
            throw new Exception('No reviews found to generate overview');
        }

        $prompt = app(EatingOutSealiacOverviewPrompt::class)->handle($eatery, $branch);

        $result = OpenAI::chat()->create([
            'model' => 'gpt-3.5-turbo-1106',
            'messages' => [
                ['role' => 'system', 'content' => $prompt],
            ],
        ]);

        /** @var string $response */
        $response = $result->choices[0]->message->content;

        SealiacOverview::query()->create([
            'wheretoeat_id' => $eatery->id,
            'nationwide_branch_id' => $branch?->id,
            'overview' => $response,
        ]);

        return $response;
    }
}
