<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Blogs;

use App\Actions\Blogs\GetBlogsForBlogIndexAction;
use App\Models\Blogs\Blog;
use App\Models\Blogs\BlogTag;
use App\ResourceCollections\Blogs\BlogListCollection;
use App\Resources\Blogs\BlogDetailCardViewResource;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetBlogsForBlogIndexActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');

        $this->withBlogs(15);
    }

    #[Test]
    public function itReturnsABlogListCollection(): void
    {
        $this->assertInstanceOf(
            BlogListCollection::class,
            $this->callAction(GetBlogsForBlogIndexAction::class),
        );
    }

    #[Test]
    public function itIsAPaginatedCollection(): void
    {
        $blogs = $this->callAction(GetBlogsForBlogIndexAction::class);

        $this->assertInstanceOf(LengthAwarePaginator::class, $blogs->resource);
    }

    #[Test]
    public function itReturns12ItemsPerPageByDefault(): void
    {
        $this->assertCount(12, $this->callAction(GetBlogsForBlogIndexAction::class));
    }

    #[Test]
    public function itCanHaveADifferentPageLimitSpecified(): void
    {
        $this->assertCount(5, $this->callAction(GetBlogsForBlogIndexAction::class, perPage: 5));
    }

    #[Test]
    public function eachItemInThePageIsABlogDetailCardViewResource(): void
    {
        $resource = $this->callAction(GetBlogsForBlogIndexAction::class)->resource->first();

        $this->assertInstanceOf(BlogDetailCardViewResource::class, $resource);
    }

    #[Test]
    public function itLoadsTheMediaAndTagsRelationship(): void
    {
        /** @var Blog $blog */
        $blog = $this->callAction(GetBlogsForBlogIndexAction::class)->resource->first()->resource;

        $this->assertTrue($blog->relationLoaded('media'));
        $this->assertTrue($blog->relationLoaded('tags'));
    }

    #[Test]
    public function itHasACommentsCount(): void
    {
        /** @var Blog $blog */
        $blog = $this->callAction(GetBlogsForBlogIndexAction::class)->resource->first()->resource;

        $this->assertArrayHasKey('comments_count', $blog->attributesToArray());
    }

    #[Test]
    public function itCanBeFilteredByBlogTag(): void
    {
        $tag = $this->create(BlogTag::class);

        Blog::query()
            ->inRandomOrder()
            ->take(5)
            ->get()
            ->each(fn (Blog $blog) => $blog->tags()->attach($tag));

        $collection = $this->callAction(GetBlogsForBlogIndexAction::class, $tag);

        $this->assertCount(5, $collection);
    }

    #[Test]
    public function itCanBeFilteredBySearch(): void
    {
        Blog::query()->first()->update(['title' => 'Test Blog Yay']);

        $collection = $this->callAction(GetBlogsForBlogIndexAction::class, search: 'test blog');

        $this->assertCount(1, $collection);
    }
}
