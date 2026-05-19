<?php

declare(strict_types=1);

namespace App\Jobs\Metrics\Orchestrators;

use App\Contracts\Metrics\MetricSource;
use Illuminate\Bus\Queueable;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

abstract class BaseOrchestratorJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct()
    {
        $this->onQueue('metrics');
    }

    /** @return MetricSource[] */
    abstract protected function sources(): array;

    abstract protected function intervalMinutes(): int;

    public function handle(): void
    {
        $delayTime = 0;

        foreach ($this->sources() as $source) {
            $source->query()
                ->with([$source->metricsRelation() => fn ($query) => $query->whereDate('date', today())])
                ->lazy()
                ->each(function (Model $model) use (&$delayTime, $source): void {
                    if ( ! $this->shouldDispatch($model, $source)) {
                        return;
                    }

                    $source->dispatch($model, $delayTime);
                    ++$delayTime;
                });
        }
    }

    protected function shouldDispatch(Model $model, MetricSource $source): bool
    {
        /** @var Collection<int, Model> $related */
        $related = $model->getRelation($source->metricsRelation());

        /** @var Model|null $metric */
        $metric = $related->first();

        if ($metric === null) {
            return true;
        }

        /** @phpstan-ignore-next-line */
        return $metric->created_at->lt(now()->subMinutes($this->intervalMinutes()));
    }

    public static function scheduleAll(Schedule $schedule): void
    {
        $schedule->job(MinuteOrchestratorJob::class)->everyMinute();
        $schedule->job(TenMinuteOrchestratorJob::class)->everyTenMinutes();
        $schedule->job(ThirtyMinuteOrchestratorJob::class)->everyThirtyMinutes();
        $schedule->job(HourlyOrchestratorJob::class)->hourly();
        $schedule->job(TwoHourOrchestratorJob::class)->everyTwoHours();
        $schedule->job(ThreeHourOrchestratorJob::class)->everyThreeHours();
        $schedule->job(SixHourOrchestratorJob::class)->everySixHours();
        $schedule->job(TwelveHourOrchestratorJob::class)->cron('0 */12 * * *');
        $schedule->job(DailyOrchestratorJob::class)->daily();
    }
}
