<?php

declare(strict_types=1);

namespace App\Jobs\Metrics\Orchestrators;

use App\Jobs\Metrics\Sources\BlogMetricSource;

class MinuteOrchestrator extends BaseOrchestrator
{
    protected function intervalMinutes(): int
    {
        return 1;
    }

    protected function sources(): array
    {
        return [
            new BlogMetricSource(
                createdAfter: now()->subHours(24),
            ),
        ];
    }
}
