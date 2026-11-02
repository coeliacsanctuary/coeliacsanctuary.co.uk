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
class CompleteReportOrRecommendation extends Action
{
    public $name = 'Complete';

    public $withoutActionEvents = true;

    public function handle(ActionFields $fields, Collection $models): void
    {
        $models->each(function (EateryRecommendation|EateryReport|EateryAlert $model): void {
            if ($model->completed) {
                return;
            }

            $model->update(['completed' => true]);
        });
    }

    /**
     * @return array
     */
    public function fields(NovaRequest $request)
    {
        return [];
    }
}
