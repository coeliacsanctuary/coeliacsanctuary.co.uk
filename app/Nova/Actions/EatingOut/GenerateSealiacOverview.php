<?php

declare(strict_types=1);

namespace App\Nova\Actions\EatingOut;

use App\Actions\EatingOut\GetSealiacEateryOverviewAction;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Fields\ActionFields;

class GenerateSealiacOverview extends Action
{
    /**
     * @param  Collection<int, Eatery|NationwideBranch>  $models
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $models->each(function (Eatery|NationwideBranch $model): void {
            $eatery = $model;
            $branch = null;

            if ($model instanceof NationwideBranch) {
                $eatery = $model->eatery;
                $branch = $model;
            }

            $eatery->sealiacOverview?->update(['invalidated' => true]);
            $branch?->sealiacOverview?->update(['invalidated' => true]);

            dispatch(fn () => app(GetSealiacEateryOverviewAction::class)->handle($eatery, $branch));
        });

        return ActionResponse::message('Sealiac overviews are queued to be generated');
    }
}
