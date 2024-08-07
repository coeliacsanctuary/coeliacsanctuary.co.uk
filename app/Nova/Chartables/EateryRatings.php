<?php

declare(strict_types=1);

namespace App\Nova\Chartables;

use App\Models\EatingOut\EateryReview;
use Carbon\Carbon;
use Jpeters8889\ApexCharts\Chartable;

class EateryRatings extends Chartable
{
    public function type(): string
    {
        return static::LINE_CHART;
    }

    public function getData(Carbon $startDate, Carbon $endDate): int|float
    {
        return EateryReview::withoutGlobalScopes()
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->count();
    }

    public function defaultDateRange(): string
    {
        return self::DATE_RANGE_PAST_2_WEEKS;
    }
}
