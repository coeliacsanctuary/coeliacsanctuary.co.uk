<?php

declare(strict_types=1);

namespace App\Nova\Actions\EatingOut;

use App\Models\EatingOut\EateryAlert;
use App\Models\EatingOut\EateryRecommendation;
use App\Models\EatingOut\EateryReport;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @codeCoverageIgnore
 */
class IgnoreReportOrRecommendation extends Action
{
    public $name = 'Ignore';

    public $withoutActionEvents = true;

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $models->each(function (EateryReport|EateryRecommendation|EateryAlert $model): void {
            if ($model->ignored) {
                return;
            }

            $model->update(['ignored' => true]);
        });
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [];
    }
}
