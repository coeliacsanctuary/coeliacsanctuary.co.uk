<?php

declare(strict_types=1);

namespace App\Nova\Actions\EatingOut;

use App\Models\EatingOut\EaterySuggestedEdit;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Http\Requests\NovaRequest;

/**
 * @codeCoverageIgnore
 */
class AcceptEdit extends Action
{
    public $name = 'Accept';

    public $withoutActionEvents = true;

    public function handle(ActionFields $fields, Collection $models): void
    {
        $models->each(function (EaterySuggestedEdit $edit): void {
            if ($edit->accepted) {
                return;
            }

            $edit->update(['accepted' => true]);
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
