<?php

declare(strict_types=1);

namespace App\Jobs\Metrics\Orchestrators;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Metrics\Sources\BlogMetricSource;
use Illuminate\Support\Carbon;

class TwoHourOrchestratorJob extends BaseOrchestratorJob implements ShouldQueue
{
    protected function intervalMinutes(): int
    {
        return Carbon::MINUTES_PER_HOUR * 2;
    }

    protected function sources(): array
    {
        return [
            new BlogMetricSource(
                createdAfter: now()->subMonths(2),
                createdBefore: now()->subMonth(),
            ),
        ];
    }
}
