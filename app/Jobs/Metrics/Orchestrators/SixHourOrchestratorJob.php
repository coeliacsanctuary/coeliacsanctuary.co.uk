<?php

declare(strict_types=1);

namespace App\Jobs\Metrics\Orchestrators;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Metrics\Sources\BlogMetricSource;
use Illuminate\Support\Carbon;

class SixHourOrchestratorJob extends BaseOrchestratorJob implements ShouldQueue
{
    protected function intervalMinutes(): int
    {
        return Carbon::MINUTES_PER_HOUR * 6;
    }

    protected function sources(): array
    {
        return [
            new BlogMetricSource(
                createdAfter: now()->subYear(),
                createdBefore: now()->subMonths(6),
            ),
        ];
    }
}
