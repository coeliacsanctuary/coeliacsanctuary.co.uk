<?php

declare(strict_types=1);

namespace App\Jobs\Metrics\Orchestrators;

use App\Metrics\Sources\BlogMetricSource;
use App\Metrics\Sources\RecipeMetricSource;
use Illuminate\Contracts\Queue\ShouldQueue;

class TenMinuteOrchestratorJob extends BaseOrchestratorJob implements ShouldQueue
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
            new RecipeMetricSource(
                createdAfter: now()->subWeek(),
                createdBefore: now()->subHours(24),
            ),
        ];
    }
}
