<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\Metrics\Blogs\BlogMetricOrchestratorJob;
use Illuminate\Console\Command;

class PrepareMetricUpdatesCommand extends Command
{
    protected $signature = 'coeliac:prepare-metric-updates';

    public function handle(): void
    {
        BlogMetricOrchestratorJob::dispatch();
    }
}
