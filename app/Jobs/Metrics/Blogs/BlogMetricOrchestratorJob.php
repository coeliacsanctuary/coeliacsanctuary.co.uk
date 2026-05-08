<?php

declare(strict_types=1);

namespace App\Jobs\Metrics\Blogs;

use App\Models\Blogs\Blog;
use App\Models\Blogs\BlogMetric;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class BlogMetricOrchestratorJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public string $queue = 'metrics';

    public function handle(): void
    {
        $delayTime = 0;

        Blog::query()
            ->latest()
            ->with(['metrics' => fn ($query) => $query->whereDate('date', today())])
            ->lazy()
            ->each(function (Blog $blog) use (&$delayTime): void {
                if ( ! $this->requiresUpdate($blog)) {
                    return;
                }

                GetBlogMetricsJob::dispatch($blog)->delay($delayTime);
                $delayTime += 15;
            });
    }

    protected function requiresUpdate(Blog $blog): bool
    {
        /** @var BlogMetric | null $metric */
        $metric = $blog->metrics->first();

        return match (true) {
            ! $metric => true,
            $blog->created_at->gte(now()->subHours(24)) => true,
            $blog->created_at->gte(now()->subWeek()) => $this->isOnSchedule($metric, 10),
            $blog->created_at->gte(now()->subWeeks(2)) => $this->isOnSchedule($metric, 30),
            $blog->created_at->gte(now()->subMonth()) => $this->isOnSchedule($metric, 60),
            $blog->created_at->gte(now()->subMonths(2)) => $this->isOnSchedule($metric, 120),
            $blog->created_at->gte(now()->subMonths(6)) => $this->isOnSchedule($metric, 180),
            $blog->created_at->gte(now()->subYear()) => $this->isOnSchedule($metric, 360),
            $blog->created_at->gte(now()->subYears(2)) => $this->isOnSchedule($metric, 720),
            default => $this->isOnSchedule($metric, 1440),
        };
    }

    protected function isOnSchedule(BlogMetric $metric, int $minutes): bool
    {
        if (now()->minute % $minutes !== 0) {
            return false;
        }

        return $metric->created_at->lt(now()->subMinutes($minutes));
    }
}
