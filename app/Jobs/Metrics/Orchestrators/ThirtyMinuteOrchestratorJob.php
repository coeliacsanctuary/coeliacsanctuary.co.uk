<?php

declare(strict_types=1);

namespace App\Jobs\Metrics\Orchestrators;

use App\Metrics\Sources\BlogMetricSource;
use App\Metrics\Sources\RecipeMetricSource;
use Illuminate\Contracts\Queue\ShouldQueue;

class ThirtyMinuteOrchestratorJob extends BaseOrchestratorJob implements ShouldQueue
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
            new RecipeMetricSource(
                createdAfter: now()->subWeeks(2),
                createdBefore: now()->subWeek(),
            ),
        ];
    }
}
