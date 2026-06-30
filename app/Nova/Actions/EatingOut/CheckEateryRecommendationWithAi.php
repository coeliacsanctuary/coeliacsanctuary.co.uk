<?php

declare(strict_types=1);

namespace App\Nova\Actions\EatingOut;

use App\Jobs\EatingOut\SendEateryRecommendationToAiJob;
use App\Models\EatingOut\EateryRecommendation;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;

class CheckEateryRecommendationWithAi extends Action
{
    /**
     * @param  Collection<EateryRecommendation>  $models
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $models->each(function (EateryRecommendation $model): void {
            SendEateryRecommendationToAiJob::dispatch($model);
        });

        return ActionResponse::message('Eatery Recommendation is queued to be checked with AI.');
    }
}
