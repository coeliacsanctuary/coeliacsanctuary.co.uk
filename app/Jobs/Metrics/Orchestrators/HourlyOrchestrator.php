<?php

declare(strict_types=1);

namespace App\Jobs\Metrics\Orchestrators;

use App\Jobs\Metrics\Sources\BlogMetricSource;
use Illuminate\Support\Carbon;

class HourlyOrchestrator extends BaseOrchestrator
{
    protected function intervalMinutes(): int
    {
        return Carbon::MINUTES_PER_HOUR;
    }

    protected function sources(): array
    {
        return [
            new BlogMetricSource(
                createdAfter: now()->subMonth(),
                createdBefore: now()->subWeeks(2),
            ),
        ];
    }
}
