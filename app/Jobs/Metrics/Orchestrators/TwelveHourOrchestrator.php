<?php

declare(strict_types=1);

namespace App\Jobs\Metrics\Orchestrators;

use App\Jobs\Metrics\Sources\BlogMetricSource;
use Illuminate\Support\Carbon;

class TwelveHourOrchestrator extends BaseOrchestrator
{
    protected function intervalMinutes(): int
    {
        return Carbon::MINUTES_PER_HOUR * 12;
    }

    protected function sources(): array
    {
        return [
            new BlogMetricSource(
                createdAfter: now()->subYears(2),
                createdBefore: now()->subYear(),
            ),
        ];
    }
}
