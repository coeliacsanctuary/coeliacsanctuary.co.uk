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

abstract class BaseOrchestrator implements ShouldQueue, ShouldBeUnique
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
                    $delayTime += 15;
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
        $schedule->job(MinuteOrchestrator::class)->everyMinute();
        $schedule->job(TenMinuteOrchestrator::class)->everyTenMinutes();
        $schedule->job(ThirtyMinuteOrchestrator::class)->everyThirtyMinutes();
        $schedule->job(HourlyOrchestrator::class)->hourly();
        $schedule->job(TwoHourOrchestrator::class)->everyTwoHours();
        $schedule->job(ThreeHourOrchestrator::class)->everyThreeHours();
        $schedule->job(SixHourOrchestrator::class)->everySixHours();
        $schedule->job(TwelveHourOrchestrator::class)->cron('0 */12 * * *');
        $schedule->job(DailyOrchestrator::class)->daily();
    }
}
