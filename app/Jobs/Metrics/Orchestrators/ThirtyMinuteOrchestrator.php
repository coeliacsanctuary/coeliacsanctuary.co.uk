<?php

declare(strict_types=1);

namespace App\Jobs\Metrics\Orchestrators;

use App\Jobs\Metrics\Sources\BlogMetricSource;

class ThirtyMinuteOrchestrator extends BaseOrchestrator
{
    protected function intervalMinutes(): int
    {
        return 30;
    }

    protected function sources(): array
    {
        return [
            new BlogMetricSource(
                createdAfter: now()->subWeeks(2),
                createdBefore: now()->subWeek(),
            ),
        ];
    }
}
