<?php

declare(strict_types=1);

namespace App\Jobs\Metrics\Orchestrators;

use App\Metrics\Sources\BlogMetricSource;
use App\Metrics\Sources\RecipeMetricSource;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Carbon;

class TwelveHourOrchestratorJob extends BaseOrchestratorJob implements ShouldQueue
{
    protected function intervalMinutes(): int
    {
        return Carbon::MINUTES_PER_HOUR * 12;
    }

    protected function sources(): array
    {
        return [
            new BlogMetricSource(
                createdAfter: now()->subYears(2),
                createdBefore: now()->subYear(),
            ),
            new RecipeMetricSource(
                createdAfter: now()->subYears(2),
                createdBefore: now()->subYear(),
            ),
        ];
    }
}
