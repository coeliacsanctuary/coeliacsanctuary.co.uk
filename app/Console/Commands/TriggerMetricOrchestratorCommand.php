<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\Metrics\Orchestrators\BaseOrchestratorJob;
use App\Jobs\Metrics\Orchestrators\DailyOrchestratorJob;
use App\Jobs\Metrics\Orchestrators\HourlyOrchestratorJob;
use App\Jobs\Metrics\Orchestrators\MinuteOrchestratorJob;
use App\Jobs\Metrics\Orchestrators\SixHourOrchestratorJob;
use App\Jobs\Metrics\Orchestrators\TenMinuteOrchestratorJob;
use App\Jobs\Metrics\Orchestrators\ThirtyMinuteOrchestratorJob;
use App\Jobs\Metrics\Orchestrators\ThreeHourOrchestratorJob;
use App\Jobs\Metrics\Orchestrators\TwelveHourOrchestratorJob;
use App\Jobs\Metrics\Orchestrators\TwoHourOrchestratorJob;
use Illuminate\Console\Command;
use Illuminate\Queue\Events\JobQueued;
use Illuminate\Support\Facades\Event;

use function Laravel\Prompts\select;
use function Laravel\Prompts\spin;

class TriggerMetricOrchestratorCommand extends Command
{
    protected $signature = 'coeliac:metrics:trigger';

    protected $description = 'Manually trigger a metric orchestrator to queue metric jobs';

    /** @var array<class-string<BaseOrchestratorJob>, string> */
    protected array $orchestrators = [
        MinuteOrchestratorJob::class => 'Minute — less than 24 hours old',
        TenMinuteOrchestratorJob::class => 'Ten Minutes — 1 day to 1 week old',
        ThirtyMinuteOrchestratorJob::class => 'Thirty Minutes — 1 to 2 weeks old',
        HourlyOrchestratorJob::class => 'Hourly — 2 weeks to 1 month old',
        TwoHourOrchestratorJob::class => 'Two Hours — 1 to 2 months old',
        ThreeHourOrchestratorJob::class => 'Three Hours — 2 to 6 months old',
        SixHourOrchestratorJob::class => 'Six Hours — 6 to 12 months old',
        TwelveHourOrchestratorJob::class => 'Twelve Hours — 1 to 2 years old',
        DailyOrchestratorJob::class => 'Daily — older than 2 years',
    ];

    public function handle(): void
    {
        /** @var class-string<BaseOrchestratorJob> $selected */
        $selected = select(
            label: 'Which orchestrator would you like to trigger?',
            options: $this->orchestrators,
        );

        $queued = 0;

        Event::listen(JobQueued::class, function (JobQueued $event) use (&$queued): void {
            if ($event->queue === 'metrics') {
                $queued++;
            }
        });

        spin(
            callback: fn () => (new $selected())->handle(),
            message: "Running {$this->orchestrators[$selected]}...",
        );

        $jobs = str('metric job')->plural($queued);

        $this->components->info("Done — {$queued} {$jobs} queued.");
    }
}
