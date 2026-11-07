<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\EatingOut\EateryTrip;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Value;
use Laravel\Nova\Metrics\ValueResult;
use Laravel\Nova\Nova;

/**
 * @codeCoverageIgnore
 */
class TripsPlanned extends Value
{
    public function calculate(NovaRequest $request): ValueResult
    {
        return $this->count($request, EateryTrip::class);
    }

    public function ranges()
    {
        return [
            7 => Nova::__('7 Days'),
            30 => Nova::__('30 Days'),
            90 => Nova::__('90 Days'),
        ];
    }

    public function name()
    {
        return 'Trips Planned';
    }
}
