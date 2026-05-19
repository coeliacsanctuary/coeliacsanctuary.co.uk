<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs\Metrics\Sources;

use App\Jobs\Metrics\Blogs\GetBlogMetricsJob;
use App\Jobs\Metrics\Sources\BlogMetricSource;
use App\Models\Blogs\Blog;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BlogMetricSourceTest extends TestCase
{
    #[Test]
    public function itReturnsOnlyBlogsNewerThanCreatedAfter(): void
    {
        $included = $this->create(Blog::class, ['created_at' => now()->subDays(3)]);
        $this->create(Blog::class, ['created_at' => now()->subDays(10)]);

        $source = new BlogMetricSource(createdAfter: now()->subWeek());

        $results = $source->query()->pluck('id');

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($included->id));
    }

    #[Test]
    public function itReturnsOnlyBlogsOlderThanCreatedBefore(): void
    {
        $this->create(Blog::class, ['created_at' => now()->subHours(12)]);
        $included = $this->create(Blog::class, ['created_at' => now()->subDays(3)]);

        $source = new BlogMetricSource(createdBefore: now()->subHours(24));

        $results = $source->query()->pluck('id');

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($included->id));
    }

    #[Test]
    public function itReturnsBlogsWithinBothBounds(): void
    {
        $this->create(Blog::class, ['created_at' => now()->subHours(12)]);
        $included = $this->create(Blog::class, ['created_at' => now()->subDays(3)]);
        $this->create(Blog::class, ['created_at' => now()->subDays(10)]);

        $source = new BlogMetricSource(
            createdAfter: now()->subWeek(),
            createdBefore: now()->subHours(24),
        );

        $results = $source->query()->pluck('id');

        $this->assertCount(1, $results);
        $this->assertTrue($results->contains($included->id));
    }

    #[Test]
    public function itReturnsAllBlogsWhenNoBoundsSet(): void
    {
        $this->create(Blog::class);
        $this->create(Blog::class);

        $source = new BlogMetricSource();

        $this->assertCount(2, $source->query()->get());
    }

    #[Test]
    public function metricsRelationReturnMetrics(): void
    {
        $source = new BlogMetricSource();

        $this->assertEquals('metrics', $source->metricsRelation());
    }

    #[Test]
    public function itDispatchesGetBlogMetricsJobWithCorrectDelay(): void
    {
        Bus::fake();

        $blog = $this->create(Blog::class);
        $source = new BlogMetricSource();

        $source->dispatch($blog, 30);

        Bus::assertDispatched(GetBlogMetricsJob::class, fn ($job) => $job->delay === 30);
    }
}
