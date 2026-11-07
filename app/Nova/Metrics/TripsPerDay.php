<?php

declare(strict_types=1);

namespace App\Nova\Metrics;

use App\Models\EatingOut\EateryTrip;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Metrics\Trend;
use Laravel\Nova\Metrics\TrendResult;
use Laravel\Nova\Nova;

/**
 * @codeCoverageIgnore
 */
class TripsPerDay extends Trend
{
    public function calculate(NovaRequest $request): TrendResult
    {
        return $this->countByDays($request, EateryTrip::class)
            ->showLatestValue()
            ->showSumValue();
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
        return 'Trips Per Day';
    }
}
