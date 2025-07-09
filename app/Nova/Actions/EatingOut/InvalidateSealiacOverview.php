<?php

declare(strict_types=1);

namespace App\Nova\Actions\EatingOut;

use App\Models\EatingOut\SealiacOverview;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\ActionResponse;
use Laravel\Nova\Actions\DestructiveAction;
use Laravel\Nova\Fields\ActionFields;

class InvalidateSealiacOverview extends DestructiveAction
{
    /**
     * @param  Collection<int, SealiacOverview>  $models
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $models->first()->update(['invalidated' => true]);

        return ActionResponse::message('Sealiac overview invalidated.');
    }
}
