<?php

declare(strict_types=1);

namespace App\Jobs\Metrics\Orchestrators;

use App\Jobs\Metrics\Sources\BlogMetricSource;
use Illuminate\Support\Carbon;

class DailyOrchestrator extends BaseOrchestrator
{
    protected function intervalMinutes(): int
    {
        return Carbon::MINUTES_PER_HOUR * Carbon::HOURS_PER_DAY;
    }

    protected function sources(): array
    {
        return [
            new BlogMetricSource(
                createdBefore: now()->subYears(2),
            ),
        ];
    }
}
