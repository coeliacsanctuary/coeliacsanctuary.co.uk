<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs\Metrics\Orchestrators;

use App\Contracts\Metrics\MetricSource;
use App\Jobs\Metrics\Blogs\GetBlogMetricsJob;
use App\Jobs\Metrics\Orchestrators\BaseOrchestrator;
use App\Jobs\Metrics\Sources\BlogMetricSource;
use App\Models\Blogs\Blog;
use App\Models\Blogs\BlogMetric;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BaseOrchestratorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();
    }

    protected function makeOrchestrator(int $intervalMinutes = 10, ?MetricSource $source = null): BaseOrchestrator
    {
        $source ??= new BlogMetricSource(
            createdAfter: now()->subWeek(),
            createdBefore: now()->subHours(24),
        );

        return new class ($intervalMinutes, $source) extends BaseOrchestrator {
            public function __construct(
                private int $minutes,
                private MetricSource $testSource,
            ) {
                parent::__construct();
            }

            protected function intervalMinutes(): int
            {
                return $this->minutes;
            }

            protected function sources(): array
            {
                return [$this->testSource];
            }
        };
    }

    #[Test]
    public function itDispatchesWhenNoTodayMetricExists(): void
    {
        $this->create(Blog::class, ['created_at' => now()->subDays(3)]);

        $this->makeOrchestrator()->handle();

        Bus::assertDispatched(GetBlogMetricsJob::class);
    }

    #[Test]
    public function itDispatchesWhenTodayMetricIsOlderThanInterval(): void
    {
        $blog = $this->create(Blog::class, ['created_at' => now()->subDays(3)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(11),
        ]);

        $this->makeOrchestrator(intervalMinutes: 10)->handle();

        Bus::assertDispatched(GetBlogMetricsJob::class);
    }

    #[Test]
    public function itDoesNotDispatchWhenTodayMetricIsFresh(): void
    {
        $blog = $this->create(Blog::class, ['created_at' => now()->subDays(3)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(9),
        ]);

        $this->makeOrchestrator(intervalMinutes: 10)->handle();

        Bus::assertNotDispatched(GetBlogMetricsJob::class);
    }

    #[Test]
    public function itIncreasesDelayBy15SecondsPerDispatchedJob(): void
    {
        $this->create(Blog::class, ['created_at' => now()->subHours(36)]);
        $this->create(Blog::class, ['created_at' => now()->subHours(48)]);
        $this->create(Blog::class, ['created_at' => now()->subHours(60)]);

        $this->makeOrchestrator()->handle();

        Bus::assertDispatched(GetBlogMetricsJob::class, 3);

        $delays = Bus::dispatched(GetBlogMetricsJob::class)
            ->map(fn ($job) => $job->delay)
            ->sort()
            ->values();

        $this->assertEquals([0, 15, 30], $delays->all());
    }

    #[Test]
    public function itOnlyDispatchesForItemsThatNeedUpdating(): void
    {
        $needsUpdate = $this->create(Blog::class, ['created_at' => now()->subDays(3)]);
        $this->create(BlogMetric::class, [
            'blog_id' => $needsUpdate->id,
            'date' => today(),
            'created_at' => now()->subMinutes(11),
        ]);

        $upToDate = $this->create(Blog::class, ['created_at' => now()->subDays(4)]);
        $this->create(BlogMetric::class, [
            'blog_id' => $upToDate->id,
            'date' => today(),
            'created_at' => now()->subMinutes(9),
        ]);

        $this->makeOrchestrator(intervalMinutes: 10)->handle();

        Bus::assertDispatched(GetBlogMetricsJob::class, 1);
    }

    #[Test]
    public function itProcessesMultipleSources(): void
    {
        $this->create(Blog::class, ['created_at' => now()->subDays(3)]);

        $sourceA = new BlogMetricSource(
            createdAfter: now()->subWeek(),
            createdBefore: now()->subHours(24),
        );

        $sourceB = new BlogMetricSource(
            createdAfter: now()->subWeek(),
            createdBefore: now()->subHours(24),
        );

        $orchestrator = new class (10, [$sourceA, $sourceB]) extends BaseOrchestrator {
            public function __construct(
                private int $minutes,
                private array $testSources,
            ) {
                parent::__construct();
            }

            protected function intervalMinutes(): int
            {
                return $this->minutes;
            }

            protected function sources(): array
            {
                return $this->testSources;
            }
        };

        $orchestrator->handle();

        Bus::assertDispatched(GetBlogMetricsJob::class, 2);
    }
}
