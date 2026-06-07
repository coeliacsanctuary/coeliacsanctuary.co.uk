<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Blogs;

use App\Actions\Blogs\GetTopBlogsForHomepageAction;
use App\Models\Blogs\Blog;
use App\Models\Blogs\BlogMetric;
use App\Resources\Blogs\BlogSimpleCardViewResource;
use Carbon\Carbon;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetTopBlogsForHomePageActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');

        $this->withBlogs();
    }

    #[Test]
    public function itCanReturnACollectionOfBlogs(): void
    {
        $this->assertInstanceOf(AnonymousResourceCollection::class, $this->callAction(GetTopBlogsForHomepageAction::class));
    }

    #[Test]
    public function itOnlyReturnsTheBlogAsACardResource(): void
    {
        $this->callAction(GetTopBlogsForHomepageAction::class)->each(function ($item): void {
            $this->assertInstanceOf(BlogSimpleCardViewResource::class, $item);
        });
    }

    #[Test]
    public function itReturnsThreeBlogs(): void
    {
        Blog::query()->take(3)->get()->each(function (Blog $blog, int $index): void {
            $this->create(BlogMetric::class, ['blog_id' => $blog->id, 'page_views' => ($index + 1) * 100]);
        });

        $this->assertCount(3, $this->callAction(GetTopBlogsForHomepageAction::class));
    }

    #[Test]
    public function itReturnsTheBlogsOrderedByPageViews(): void
    {
        $this->create(BlogMetric::class, ['blog_id' => 5, 'page_views' => 100]);
        $this->create(BlogMetric::class, ['blog_id' => 7, 'page_views' => 300]);
        $this->create(BlogMetric::class, ['blog_id' => 9, 'page_views' => 200]);

        $blogTitles = $this->callAction(GetTopBlogsForHomepageAction::class)
            ->map(fn (BlogSimpleCardViewResource $blog) => $blog->title)
            ->values()
            ->toArray();

        $this->assertSame(['Blog 6', 'Blog 8', 'Blog 4'], $blogTitles);
    }

    #[Test]
    public function itOnlyConsidersMetricsFromTheLastDay(): void
    {
        $this->create(BlogMetric::class, ['blog_id' => 1, 'page_views' => 1000, 'date' => Carbon::now()->subDays(2)]);
        $this->create(BlogMetric::class, ['blog_id' => 5, 'page_views' => 300]);
        $this->create(BlogMetric::class, ['blog_id' => 7, 'page_views' => 200]);
        $this->create(BlogMetric::class, ['blog_id' => 9, 'page_views' => 100]);

        $blogTitles = $this->callAction(GetTopBlogsForHomepageAction::class)
            ->map(fn (BlogSimpleCardViewResource $blog) => $blog->title);

        $this->assertNotContains('Blog 0', $blogTitles);
    }

    #[Test]
    public function itDoesntReturnBlogsThatArentLive(): void
    {
        $this->create(BlogMetric::class, ['blog_id' => 1, 'page_views' => 300]);
        $this->create(BlogMetric::class, ['blog_id' => 2, 'page_views' => 200]);
        $this->create(BlogMetric::class, ['blog_id' => 3, 'page_views' => 100]);

        Blog::query()->find(1)->update(['live' => false]);

        $blogTitles = $this->callAction(GetTopBlogsForHomepageAction::class)
            ->map(fn (BlogSimpleCardViewResource $blog) => $blog->title);

        $this->assertNotContains('Blog 0', $blogTitles);
        $this->assertContains('Blog 1', $blogTitles);
    }

    #[Test]
    public function itCachesTheBlogs(): void
    {
        $this->assertFalse(Cache::has('top-blogs'));

        $blogs = $this->callAction(GetTopBlogsForHomepageAction::class);

        $this->assertTrue(Cache::has('top-blogs'));
        $this->assertSame($blogs, Cache::get('top-blogs'));
    }

    #[Test]
    public function itLoadsTheBlogsFromTheCache(): void
    {
        DB::enableQueryLog();

        $this->callAction(GetTopBlogsForHomepageAction::class);

        // Blogs (with metrics sum subquery) and media relation
        $this->assertCount(2, DB::getQueryLog());

        $this->callAction(GetTopBlogsForHomepageAction::class);

        $this->assertCount(2, DB::getQueryLog());
    }
}
