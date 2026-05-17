<?php

declare(strict_types=1);

namespace App\Actions\EatingOut;

use App\Ai\Agents\SealiacEateryOverviewAgent;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Models\SealiacOverview;
use Exception;

class GetSealiacEateryOverviewAction
{
    public function handle(Eatery $eatery, ?NationwideBranch $branch = null): SealiacOverview
    {
        if ($branch?->sealiacOverview) {
            return $branch->sealiacOverview;
        }

        if ( ! $branch && $eatery->sealiacOverview) {
            return $eatery->sealiacOverview;
        }

        $reviewCheck = ($branch ?: $eatery)->reviews()
            ->where('approved', true)
            ->whereNot('review', '')
            ->count();

        if ($reviewCheck === 0) {
            if ($branch && $eatery->sealiacOverview) {
                return $eatery->sealiacOverview;
            }

            throw new Exception('No reviews found to generate overview');
        }

        $response = (new SealiacEateryOverviewAgent($eatery, $branch))->prompt('Generate your overview.');

        return SealiacOverview::query()->create([
            'model_type' => $branch ? NationwideBranch::class : Eatery::class,
            'model_id' => $branch->id ?? $eatery->id,
            'overview' => $response->text,
        ]);
    }
}
