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
class TripsRevisited extends Value
{
    public function calculate(NovaRequest $request): ValueResult
    {
        return $this->count(
            $request,
            EateryTrip::query()->whereRaw('last_visited_at > DATE_ADD(created_at, INTERVAL 1 HOUR)'),
            null,
            'last_visited_at',
        );
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
        return 'Trips Revisited';
    }
}
