<?php

declare(strict_types=1);

namespace App\Nova\Actions\EatingOut;

use App\Models\EatingOut\EaterySuggestedEdit;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

class AcceptEdit extends Action
{
    public $name = 'Accept';

    public $withoutActionEvents = true;

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $models->each(function (EaterySuggestedEdit $edit): void {
            if ($edit->accepted) {
                return;
            }

            $edit->update(['accepted' => true]);
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
