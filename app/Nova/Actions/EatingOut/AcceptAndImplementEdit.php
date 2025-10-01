<?php

declare(strict_types=1);

namespace App\Nova\Actions\EatingOut;

use App\Models\EatingOut\EaterySuggestedEdit;
use App\Support\EatingOut\SuggestEdits\SuggestedEditProcessor;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @codeCoverageIgnore
 */
class AcceptAndImplementEdit extends Action
{
    public $name = 'Accept and Implement';

    public $withoutActionEvents = true;

    /**
     * @param  Collection<int, EaterySuggestedEdit>  $models
     */
    public function handle(ActionFields $fields, Collection $models): void
    {
        $models->each(function (EaterySuggestedEdit $edit): void {
            if ($edit->accepted) {
                return;
            }

            $fieldProcessor = app(SuggestedEditProcessor::class)->resolveEditableField($edit->field, $edit->value);

            $fieldProcessor->commitSuggestedValue($edit->eatery);

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
