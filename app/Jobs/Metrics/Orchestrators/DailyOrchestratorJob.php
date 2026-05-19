<?php

declare(strict_types=1);

namespace App\Jobs\Metrics\Orchestrators;

use Illuminate\Contracts\Queue\ShouldQueue;
use App\Metrics\Sources\BlogMetricSource;
use Illuminate\Support\Carbon;

class DailyOrchestratorJob extends BaseOrchestratorJob implements ShouldQueue
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
