<?php

declare(strict_types=1);

namespace App\Jobs\Metrics\Orchestrators;

use App\Metrics\Sources\BlogMetricSource;
use App\Metrics\Sources\RecipeMetricSource;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Carbon;

class HourlyOrchestratorJob extends BaseOrchestratorJob implements ShouldQueue
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
            new RecipeMetricSource(
                createdAfter: now()->subMonth(),
                createdBefore: now()->subWeeks(2),
            ),
        ];
    }
}
