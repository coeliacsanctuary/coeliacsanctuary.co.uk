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

    // --- 10-minute interval (blogs < 1 week old) ---

    #[Test]
    public function itDispatchesForBlogWithinLastWeekWhenMetricIsOlderThan10Minutes(): void
    {
        $this->travelTo(now()->setMinute(20)->setSecond(0));

        $blog = $this->create(Blog::class, ['created_at' => now()->subDays(3)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(11),
        ]);

        $this->runOrchestrator();

        Bus::assertDispatched(GetBlogMetricsJob::class);
    }

    #[Test]
    public function itDoesNotDispatchForBlogWithinLastWeekWhenMetricIsRecent(): void
    {
        $this->travelTo(now()->setMinute(20)->setSecond(0));

        $blog = $this->create(Blog::class, ['created_at' => now()->subDays(3)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(9),
        ]);

        $this->runOrchestrator();

        Bus::assertNotDispatched(GetBlogMetricsJob::class);
    }

    #[Test]
    public function itDoesNotDispatchForBlogWithinLastWeekWhenMetricIsOlderThan10MinutesButNotAtScheduledMinute(): void
    {
        $this->travelTo(now()->setMinute(7)->setSecond(0));

        $blog = $this->create(Blog::class, ['created_at' => now()->subDays(3)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(11),
        ]);

        $this->runOrchestrator();

        Bus::assertNotDispatched(GetBlogMetricsJob::class);
    }

    // --- 30-minute interval (blogs < 2 weeks old) ---

    #[Test]
    public function itDispatchesForBlogWithinLastTwoWeeksWhenMetricIsOlderThan30Minutes(): void
    {
        $this->travelTo(now()->setMinute(30)->setSecond(0));

        $blog = $this->create(Blog::class, ['created_at' => now()->subDays(10)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(31),
        ]);

        $this->runOrchestrator();

        Bus::assertDispatched(GetBlogMetricsJob::class);
    }

    #[Test]
    public function itDoesNotDispatchForBlogWithinLastTwoWeeksWhenMetricIsRecent(): void
    {
        $this->travelTo(now()->setMinute(30)->setSecond(0));

        $blog = $this->create(Blog::class, ['created_at' => now()->subDays(10)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(29),
        ]);

        $this->runOrchestrator();

        Bus::assertNotDispatched(GetBlogMetricsJob::class);
    }

    #[Test]
    public function itDoesNotDispatchForBlogWithinLastTwoWeeksWhenMetricIsOlderThan30MinutesButNotAtScheduledMinute(): void
    {
        $this->travelTo(now()->setMinute(7)->setSecond(0));

        $blog = $this->create(Blog::class, ['created_at' => now()->subDays(10)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(31),
        ]);

        $this->runOrchestrator();

        Bus::assertNotDispatched(GetBlogMetricsJob::class);
    }

    // --- 60-minute interval (blogs < 1 month old) ---

    #[Test]
    public function itDispatchesForBlogWithinLastMonthWhenMetricIsOlderThan60Minutes(): void
    {
        $this->travelTo(now()->startOfHour());

        $blog = $this->create(Blog::class, ['created_at' => now()->subDays(20)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(61),
        ]);

        $this->runOrchestrator();

        Bus::assertDispatched(GetBlogMetricsJob::class);
    }

    #[Test]
    public function itDoesNotDispatchForBlogWithinLastMonthWhenMetricIsRecent(): void
    {
        $this->travelTo(now()->startOfHour());

        $blog = $this->create(Blog::class, ['created_at' => now()->subDays(20)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(59),
        ]);

        $this->runOrchestrator();

        Bus::assertNotDispatched(GetBlogMetricsJob::class);
    }

    #[Test]
    public function itDoesNotDispatchForBlogWithinLastMonthWhenMetricIsOlderThan60MinutesButNotAtScheduledMinute(): void
    {
        $this->travelTo(now()->setMinute(7)->setSecond(0));

        $blog = $this->create(Blog::class, ['created_at' => now()->subDays(20)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(61),
        ]);

        $this->runOrchestrator();

        Bus::assertNotDispatched(GetBlogMetricsJob::class);
    }

    // --- 120-minute interval (blogs < 2 months old) ---

    #[Test]
    public function itDispatchesForBlogWithinLast2MonthsWhenMetricIsOlderThan120Minutes(): void
    {
        $this->travelTo(now()->setHour(2)->startOfHour());

        $blog = $this->create(Blog::class, ['created_at' => now()->subWeeks(6)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(121),
        ]);

        $this->runOrchestrator();

        Bus::assertDispatched(GetBlogMetricsJob::class);
    }

    #[Test]
    public function itDoesNotDispatchForBlogWithinLast2MonthsWhenMetricIsRecent(): void
    {
        $this->travelTo(now()->setHour(2)->startOfHour());

        $blog = $this->create(Blog::class, ['created_at' => now()->subWeeks(6)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(119),
        ]);

        $this->runOrchestrator();

        Bus::assertNotDispatched(GetBlogMetricsJob::class);
    }

    #[Test]
    public function itDoesNotDispatchForBlogWithinLast2MonthsWhenMetricIsOlderThan120MinutesButNotAtScheduledMinute(): void
    {
        $this->travelTo(now()->setMinute(7)->setSecond(0));

        $blog = $this->create(Blog::class, ['created_at' => now()->subWeeks(6)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(121),
        ]);

        $this->runOrchestrator();

        Bus::assertNotDispatched(GetBlogMetricsJob::class);
    }

    // --- 180-minute interval (blogs < 6 months old) ---

    #[Test]
    public function itDispatchesForBlogWithinLast6MonthsWhenMetricIsOlderThan180Minutes(): void
    {
        $this->travelTo(now()->setHour(3)->startOfHour());

        $blog = $this->create(Blog::class, ['created_at' => now()->subMonths(3)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(181),
        ]);

        $this->runOrchestrator();

        Bus::assertDispatched(GetBlogMetricsJob::class);
    }

    #[Test]
    public function itDoesNotDispatchForBlogWithinLast6MonthsWhenMetricIsRecent(): void
    {
        $this->travelTo(now()->setHour(3)->startOfHour());

        $blog = $this->create(Blog::class, ['created_at' => now()->subMonths(3)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(179),
        ]);

        $this->runOrchestrator();

        Bus::assertNotDispatched(GetBlogMetricsJob::class);
    }

    #[Test]
    public function itDoesNotDispatchForBlogWithinLast6MonthsWhenMetricIsOlderThan180MinutesButNotAtScheduledMinute(): void
    {
        $this->travelTo(now()->setMinute(7)->setSecond(0));

        $blog = $this->create(Blog::class, ['created_at' => now()->subMonths(3)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(181),
        ]);

        $this->runOrchestrator();

        Bus::assertNotDispatched(GetBlogMetricsJob::class);
    }

    // --- 360-minute interval (blogs < 1 year old) ---

    #[Test]
    public function itDispatchesForBlogWithinLastYearWhenMetricIsOlderThan360Minutes(): void
    {
        $this->travelTo(now()->setHour(6)->startOfHour());

        $blog = $this->create(Blog::class, ['created_at' => now()->subMonths(8)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(361),
        ]);

        $this->runOrchestrator();

        Bus::assertDispatched(GetBlogMetricsJob::class);
    }

    #[Test]
    public function itDoesNotDispatchForBlogWithinLastYearWhenMetricIsRecent(): void
    {
        $this->travelTo(now()->setHour(6)->startOfHour());

        $blog = $this->create(Blog::class, ['created_at' => now()->subMonths(8)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(359),
        ]);

        $this->runOrchestrator();

        Bus::assertNotDispatched(GetBlogMetricsJob::class);
    }

    #[Test]
    public function itDoesNotDispatchForBlogWithinLastYearWhenMetricIsOlderThan360MinutesButNotAtScheduledMinute(): void
    {
        $this->travelTo(now()->setMinute(7)->setSecond(0));

        $blog = $this->create(Blog::class, ['created_at' => now()->subMonths(8)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(361),
        ]);

        $this->runOrchestrator();

        Bus::assertNotDispatched(GetBlogMetricsJob::class);
    }

    // --- 720-minute interval (blogs < 2 years old) ---

    #[Test]
    public function itDispatchesForBlogWithinLast2YearsWhenMetricIsOlderThan720Minutes(): void
    {
        $this->travelTo(now()->setHour(12)->startOfHour());

        $blog = $this->create(Blog::class, ['created_at' => now()->subMonths(18)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(721),
        ]);

        $this->runOrchestrator();

        Bus::assertDispatched(GetBlogMetricsJob::class);
    }

    #[Test]
    public function itDoesNotDispatchForBlogWithinLast2YearsWhenMetricIsRecent(): void
    {
        $this->travelTo(now()->setHour(12)->startOfHour());

        $blog = $this->create(Blog::class, ['created_at' => now()->subMonths(18)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(719),
        ]);

        $this->runOrchestrator();

        Bus::assertNotDispatched(GetBlogMetricsJob::class);
    }

    #[Test]
    public function itDoesNotDispatchForBlogWithinLast2YearsWhenMetricIsOlderThan720MinutesButNotAtScheduledMinute(): void
    {
        $this->travelTo(now()->setMinute(7)->setSecond(0));

        $blog = $this->create(Blog::class, ['created_at' => now()->subMonths(18)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(721),
        ]);

        $this->runOrchestrator();

        Bus::assertNotDispatched(GetBlogMetricsJob::class);
    }

    // --- 1440-minute interval (blogs older than 2 years) ---

    #[Test]
    public function itDispatchesForBlogOlderThan2YearsWhenMetricIsOlderThan1440Minutes(): void
    {
        $this->travelTo(now()->startOfDay());

        $blog = $this->create(Blog::class, ['created_at' => now()->subYears(3)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(1441),
        ]);

        $this->runOrchestrator();

        Bus::assertDispatched(GetBlogMetricsJob::class);
    }

    #[Test]
    public function itDoesNotDispatchForBlogOlderThan2YearsWhenMetricIsRecent(): void
    {
        $this->travelTo(now()->startOfDay());

        $blog = $this->create(Blog::class, ['created_at' => now()->subYears(3)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(1439),
        ]);

        $this->runOrchestrator();

        Bus::assertNotDispatched(GetBlogMetricsJob::class);
    }

    #[Test]
    public function itDoesNotDispatchForBlogOlderThan2YearsWhenMetricIsOlderThan1440MinutesButNotAtScheduledMinute(): void
    {
        $this->travelTo(now()->setMinute(7)->setSecond(0));

        $blog = $this->create(Blog::class, ['created_at' => now()->subYears(3)]);

        $this->create(BlogMetric::class, [
            'blog_id' => $blog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(1441),
        ]);

        $this->runOrchestrator();

        Bus::assertNotDispatched(GetBlogMetricsJob::class);
    }

    // --- Combined + delay ---

    #[Test]
    public function itOnlyDispatchesForBlogsThatRequireUpdate(): void
    {
        $this->travelTo(now()->startOfDay());

        $blogNeedingUpdate = $this->create(Blog::class, ['created_at' => now()->subDays(3)]);
        $this->create(BlogMetric::class, [
            'blog_id' => $blogNeedingUpdate->id,
            'date' => today(),
            'created_at' => now()->subMinutes(11),
        ]);

        $oldBlogNeedingUpdate = $this->create(Blog::class, ['created_at' => now()->subYears(3)]);
        $this->create(BlogMetric::class, [
            'blog_id' => $oldBlogNeedingUpdate->id,
            'date' => today(),
            'created_at' => now()->subMinutes(1441),
        ]);

        $upToDateBlog = $this->create(Blog::class, ['created_at' => now()->subYears(3)]);
        $this->create(BlogMetric::class, [
            'blog_id' => $upToDateBlog->id,
            'date' => today(),
            'created_at' => now()->subMinutes(1439),
        ]);

        $this->runOrchestrator();

        Bus::assertDispatched(GetBlogMetricsJob::class, 2);
    }

    #[Test]
    public function itIncreasesDelayBy15SecondsForEachDispatchedJob(): void
    {
        $blog1 = $this->create(Blog::class, ['created_at' => now()->subHours(12)]);
        $blog2 = $this->create(Blog::class, ['created_at' => now()->subHours(12)]);
        $blog3 = $this->create(Blog::class, ['created_at' => now()->subHours(12)]);

        $this->runOrchestrator();

        Bus::assertDispatched(GetBlogMetricsJob::class, 3);

        $delays = Bus::dispatched(GetBlogMetricsJob::class)
            ->map(fn ($job) => $job->delay)
            ->sort()
            ->values();

        $this->assertEquals([0, 15, 30], $delays->all());
    }
}
