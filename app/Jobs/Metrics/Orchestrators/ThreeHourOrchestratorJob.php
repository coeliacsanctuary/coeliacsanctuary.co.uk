<?php

declare(strict_types=1);

namespace App\Jobs\Metrics\Orchestrators;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Metrics\Sources\BlogMetricSource;
use Illuminate\Support\Carbon;

class ThreeHourOrchestratorJob extends BaseOrchestratorJob implements ShouldQueue
{
    protected function intervalMinutes(): int
    {
        return Carbon::MINUTES_PER_HOUR * 3;
    }

    protected function sources(): array
    {
        return [
            new BlogMetricSource(
                createdAfter: now()->subMonths(6),
                createdBefore: now()->subMonths(2),
            ),
        ];
    }
}
