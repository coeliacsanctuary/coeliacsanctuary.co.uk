<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs\Metrics\Blogs;

use App\Jobs\Metrics\Blogs\GetBlogMetricsJob;
use App\Models\Blogs\Blog;
use App\Models\Blogs\BlogMetric;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetBlogMetricsJobTest extends TestCase
{
    protected Blog $blog;

    protected function setUp(): void
    {
        parent::setUp();

        $this->blog = $this->create(Blog::class);

        Http::fake([
            '*' => Http::response([
                'data' => [
                    'views' => 100,
                    'comment_views' => 10,
                    'detail_card_views' => 20,
                    'collection_card_views' => 5,
                ],
            ]),
        ]);
    }

    protected function runJob(): void
    {
        (new GetBlogMetricsJob($this->blog))->handle();
    }

    #[Test]
    public function itCreatesABlogMetricForTodayWhenNoneExists(): void
    {
        $this->assertDatabaseEmpty(BlogMetric::class);

        $this->runJob();

        $this->assertDatabaseCount(BlogMetric::class, 1);
    }

    #[Test]
    public function itUpdatesAnExistingBlogMetricForToday(): void
    {
        $this->create(BlogMetric::class, [
            'blog_id' => $this->blog->id,
            'date' => today(),
            'page_views' => 0,
        ]);

        $this->runJob();

        $this->assertDatabaseCount(BlogMetric::class, 1);
    }

    #[Test]
    public function itStoresTheCorrectPageViews(): void
    {
        $this->runJob();

        $metric = BlogMetric::query()->first();

        $this->assertEquals(100, $metric->page_views);
    }

    #[Test]
    public function itStoresTheCorrectPageCommentViews(): void
    {
        $this->runJob();

        $metric = BlogMetric::query()->first();

        $this->assertEquals(10, $metric->page_comment_views);
    }

    #[Test]
    public function itStoresTheCorrectDetailCardViews(): void
    {
        $this->runJob();

        $metric = BlogMetric::query()->first();

        $this->assertEquals(20, $metric->detail_card_views);
    }

    #[Test]
    public function itStoresTheCorrectCollectionCardViews(): void
    {
        $this->runJob();

        $metric = BlogMetric::query()->first();

        $this->assertEquals(5, $metric->collection_card_views);
    }

    #[Test]
    public function itSendsTheCorrectPagePathToJourneyTracker(): void
    {
        $this->runJob();

        Http::assertSent(function (Request $request) {
            $data = $request->data();
            $pageDescriptor = collect($data['data'])->firstWhere('as', 'views');

            return $pageDescriptor['has']['pages'][0]['path'] === mb_trim($this->blog->link, '/');
        });
    }

    #[Test]
    public function itSendsTheCorrectCommentViewsEventToJourneyTracker(): void
    {
        $this->runJob();

        Http::assertSent(function (Request $request) {
            $data = $request->data();
            $descriptor = collect($data['data'])->firstWhere('as', 'comment_views');
            $event = $descriptor['has']['events'][0];

            return $event['type'] === 'scrolled_into_view'
                && $event['identifier'] === 'CommentsCard'
                && $event['parameters']['page'] === 'blog'
                && $event['parameters']['id'] === $this->blog->id;
        });
    }

    #[Test]
    public function itSendsTheCorrectDetailCardViewsEventToJourneyTracker(): void
    {
        $this->runJob();

        Http::assertSent(function (Request $request) {
            $data = $request->data();
            $descriptor = collect($data['data'])->firstWhere('as', 'detail_card_views');
            $event = $descriptor['has']['events'][0];

            return $event['type'] === 'scrolled_into_view'
                && $event['identifier'] === 'BlogDetailCard'
                && $event['parameters']['title'] === $this->blog->title;
        });
    }

    #[Test]
    public function itSendsTheCorrectCollectionCardViewsEventToJourneyTracker(): void
    {
        $this->runJob();

        Http::assertSent(function (Request $request) {
            $data = $request->data();
            $descriptor = collect($data['data'])->firstWhere('as', 'collection_card_views');
            $event = $descriptor['has']['events'][0];

            return $event['type'] === 'scrolled_into_view'
                && $event['identifier'] === 'CollectionItemCard'
                && $event['parameters']['title'] === $this->blog->title
                && $event['parameters']['type'] === 'Blog';
        });
    }
}
