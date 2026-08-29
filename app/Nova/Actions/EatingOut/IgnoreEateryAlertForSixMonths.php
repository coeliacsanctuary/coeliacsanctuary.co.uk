<?php

declare(strict_types=1);

namespace App\Nova\Actions\EatingOut;

use App\Models\EatingOut\EateryAlert;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @codeCoverageIgnore
 */
class IgnoreEateryAlertForSixMonths extends Action
{
    public $name = 'Ignore for 6 Months';

    public $withoutActionEvents = true;

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $models->each(function (EateryAlert $model): void {
            if ($model->ignored) {
                return;
            }

            $model->update(['ignored' => true]);

            $model->eatery->check()->updateOrCreate(
                ['wheretoeat_id' => $model->eatery->id],
                ['website_check_disabled_until' => now()->addMonths(6)],
            );
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
