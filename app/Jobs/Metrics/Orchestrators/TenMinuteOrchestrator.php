<?php

declare(strict_types=1);

namespace App\Jobs\Metrics\Orchestrators;

use App\Jobs\Metrics\Sources\BlogMetricSource;

class TenMinuteOrchestrator extends BaseOrchestrator
{
    protected function intervalMinutes(): int
    {
        return 10;
    }

    protected function sources(): array
    {
        return [
            new BlogMetricSource(
                createdAfter: now()->subWeek(),
                createdBefore: now()->subHours(24),
            ),
        ];
    }
}
