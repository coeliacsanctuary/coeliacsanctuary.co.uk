<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs\Metrics\Blogs;

use App\Jobs\Metrics\Blogs\BlogMetricOrchestratorJob;
use App\Jobs\Metrics\Blogs\GetBlogMetricsJob;
use App\Models\Blogs\Blog;
use App\Models\Blogs\BlogMetric;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BlogMetricOrchestratorJobTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();
    }

    protected function runOrchestrator(): void
    {
        (new BlogMetricOrchestratorJob())->handle();
    }

    #[Test]
    public function itDispatchesForBlogWithNoMetricForToday(): void
    {
        $this->create(Blog::class);

        $this->runOrchestrator();

        Bus::assertDispatched(GetBlogMetricsJob::class);
    }

    #[Test]
    public function itDispatchesForBlogCreatedWithinLast24HoursEvenWhenMetricExists(): void
    {
        $blog = $this->create(Blog::class, ['created_at' => now()->subHours(12)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinute(),
        ]);

        $this->runOrchestrator();

        Bus::assertDispatched(GetBlogMetricsJob::class);
    }

    #[Test]
    public function itDispatchesForBlogWithinLastWeekWhenMetricIsOlderThan5Minutes(): void
    {
        $this->travelTo(now()->setMinute(10)->setSecond(0));

        $blog = $this->create(Blog::class, ['created_at' => now()->subDays(3)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(6),
        ]);

        $this->runOrchestrator();

        Bus::assertDispatched(GetBlogMetricsJob::class);
    }

    #[Test]
    public function itDoesNotDispatchForBlogWithinLastWeekWhenMetricIsRecent(): void
    {
        $this->travelTo(now()->setMinute(10)->setSecond(0));

        $blog = $this->create(Blog::class, ['created_at' => now()->subDays(3)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(4),
        ]);

        $this->runOrchestrator();

        Bus::assertNotDispatched(GetBlogMetricsJob::class);
    }

    #[Test]
    public function itDoesNotDispatchForBlogWithinLastWeekWhenMetricIsOlderThan5MinutesButNotAtScheduledMinute(): void
    {
        $this->travelTo(now()->setMinute(7)->setSecond(0));

        $blog = $this->create(Blog::class, ['created_at' => now()->subDays(3)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(6),
        ]);

        $this->runOrchestrator();

        Bus::assertNotDispatched(GetBlogMetricsJob::class);
    }

    #[Test]
    public function itDispatchesForBlogWithinLastTwoWeeksWhenMetricIsOlderThan10Minutes(): void
    {
        $this->travelTo(now()->setMinute(20)->setSecond(0));

        $blog = $this->create(Blog::class, ['created_at' => now()->subDays(10)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(11),
        ]);

        $this->runOrchestrator();

        Bus::assertDispatched(GetBlogMetricsJob::class);
    }

    #[Test]
    public function itDoesNotDispatchForBlogWithinLastTwoWeeksWhenMetricIsRecent(): void
    {
        $this->travelTo(now()->setMinute(20)->setSecond(0));

        $blog = $this->create(Blog::class, ['created_at' => now()->subDays(10)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(9),
        ]);

        $this->runOrchestrator();

        Bus::assertNotDispatched(GetBlogMetricsJob::class);
    }

    #[Test]
    public function itDoesNotDispatchForBlogWithinLastTwoWeeksWhenMetricIsOlderThan10MinutesButNotAtScheduledMinute(): void
    {
        $this->travelTo(now()->setMinute(7)->setSecond(0));

        $blog = $this->create(Blog::class, ['created_at' => now()->subDays(10)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(11),
        ]);

        $this->runOrchestrator();

        Bus::assertNotDispatched(GetBlogMetricsJob::class);
    }

    #[Test]
    public function itDispatchesForBlogWithinLastMonthWhenMetricIsOlderThan30Minutes(): void
    {
        $this->travelTo(now()->setMinute(30)->setSecond(0));

        $blog = $this->create(Blog::class, ['created_at' => now()->subDays(20)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(31),
        ]);

        $this->runOrchestrator();

        Bus::assertDispatched(GetBlogMetricsJob::class);
    }

    #[Test]
    public function itDoesNotDispatchForBlogWithinLastMonthWhenMetricIsRecent(): void
    {
        $this->travelTo(now()->setMinute(30)->setSecond(0));

        $blog = $this->create(Blog::class, ['created_at' => now()->subDays(20)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(29),
        ]);

        $this->runOrchestrator();

        Bus::assertNotDispatched(GetBlogMetricsJob::class);
    }

    #[Test]
    public function itDoesNotDispatchForBlogWithinLastMonthWhenMetricIsOlderThan30MinutesButNotAtScheduledMinute(): void
    {
        $this->travelTo(now()->setMinute(7)->setSecond(0));

        $blog = $this->create(Blog::class, ['created_at' => now()->subDays(20)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(31),
        ]);

        $this->runOrchestrator();

        Bus::assertNotDispatched(GetBlogMetricsJob::class);
    }

    #[Test]
    public function itDispatchesForOldBlogWhenMetricIsOlderThan1Hour(): void
    {
        $this->travelTo(now()->startOfHour());

        $blog = $this->create(Blog::class, ['created_at' => now()->subMonths(2)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(61),
        ]);

        $this->runOrchestrator();

        Bus::assertDispatched(GetBlogMetricsJob::class);
    }

    #[Test]
    public function itDoesNotDispatchForOldBlogWhenMetricIsRecent(): void
    {
        $this->travelTo(now()->startOfHour());

        $blog = $this->create(Blog::class, ['created_at' => now()->subMonths(2)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(59),
        ]);

        $this->runOrchestrator();

        Bus::assertNotDispatched(GetBlogMetricsJob::class);
    }

    #[Test]
    public function itDoesNotDispatchForOldBlogWhenMetricIsOlderThan1HourButNotAtScheduledMinute(): void
    {
        $this->travelTo(now()->setMinute(7)->setSecond(0));

        $blog = $this->create(Blog::class, ['created_at' => now()->subMonths(2)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(61),
        ]);

        $this->runOrchestrator();

        Bus::assertNotDispatched(GetBlogMetricsJob::class);
    }

    #[Test]
    public function itOnlyDispatchesForBlogsThatRequireUpdate(): void
    {
        $this->travelTo(now()->startOfHour());

        $blogNeedingUpdate = $this->create(Blog::class, ['created_at' => now()->subDays(3)]);
        $this->create(BlogMetric::class, [
            'blog_id' => $blogNeedingUpdate->id,
            'date' => today(),
            'created_at' => now()->subMinutes(6),
        ]);

        $oldBlogNeedingUpdate = $this->create(Blog::class, ['created_at' => now()->subMonths(2)]);
        $this->create(BlogMetric::class, [
            'blog_id' => $oldBlogNeedingUpdate->id,
            'date' => today(),
            'created_at' => now()->subMinutes(61),
        ]);

        $upToDateBlog = $this->create(Blog::class, ['created_at' => now()->subMonths(2)]);
        $this->create(BlogMetric::class, [
            'blog_id' => $upToDateBlog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(30),
        ]);

        $this->runOrchestrator();

        Bus::assertDispatched(GetBlogMetricsJob::class, 2);
    }
}
