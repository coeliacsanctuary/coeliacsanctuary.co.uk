<?php

declare(strict_types=1);

namespace App\Nova\Actions\Shop;

use Laravel\Nova\Actions\ActionResponse;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;

/**
 * @codeCoverageIgnore
 */
class OpenDispatchSlip extends Action
{
    public function handle(ActionFields $fields, Collection $models): Action|ActionResponse
    {
        return Action::openInNewTab("/cs-adm/order-dispatch-slip/{$models->first()->id}");
    }
}
