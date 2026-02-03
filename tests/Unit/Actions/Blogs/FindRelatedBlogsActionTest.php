<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Blogs;

use App\Actions\Blogs\FindRelatedBlogsAction;
use App\Models\Blogs\Blog;
use App\Models\Blogs\BlogTag;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class FindRelatedBlogsActionTest extends TestCase
{
    #[Test]
    public function itUsesThePrimaryTagIfSet(): void
    {
        $blog = $this->build(Blog::class)
            ->hasAttached($this->build(BlogTag::class)->count(2), relationship: 'tags')
            ->create();

        $blog->update(['primary_tag_id' => $blog->tags->first()->id]);

        $otherBlog = $this->create(Blog::class);
        $otherBlog->tags()->attach($blog->tags->first());

        $relatedBlogs = app(FindRelatedBlogsAction::class)->handle($blog);

        $this->assertNotEmpty($relatedBlogs);
    }

    #[Test]
    public function itDoesntIncludeTheGivenBlogWhenPullingThePrimaryTag(): void
    {
        $blog = $this->build(Blog::class)
            ->hasAttached($this->build(BlogTag::class), relationship: 'tags')
            ->create();

        $blog->update(['primary_tag_id' => $blog->tags->first()->id]);

        $relatedBlogs = app(FindRelatedBlogsAction::class)->handle($blog);

        $this->assertEmpty($relatedBlogs);
    }

    #[Test]
    public function itDoesntIncludeTheGivenBlogWhenPullingFromAllTags(): void
    {
        $blog = $this->build(Blog::class)
            ->hasAttached($this->build(BlogTag::class), relationship: 'tags')
            ->create();

        $relatedBlogs = app(FindRelatedBlogsAction::class)->handle($blog);

        $this->assertEmpty($relatedBlogs);
    }

    #[Test]
    public function itGetsOtherBlogsForThatBlogsTags(): void
    {
        $tag = $this->create(BlogTag::class);

        $blogs = $this->build(Blog::class)
            ->count(2)
            ->hasAttached($tag, relationship: 'tags')
            ->create();

        $relatedBlogs = app(FindRelatedBlogsAction::class)->handle($blogs->first());

        $this->assertCount(1, $relatedBlogs);
        $this->assertTrue($relatedBlogs->contains('id', $blogs->last()->id));
    }

    #[Test]
    public function itGetsOtherBlogsForThatBlogsPrimaryTag(): void
    {
        $tag = $this->create(BlogTag::class);

        $blogs = $this->build(Blog::class)
            ->count(2)
            ->hasAttached($tag, relationship: 'tags')
            ->create();

        $blogs->first()->update(['primary_tag_id' => $tag->id]);

        $relatedBlogs = app(FindRelatedBlogsAction::class)->handle($blogs->first());

        $this->assertCount(1, $relatedBlogs);
        $this->assertTrue($relatedBlogs->contains('id', $blogs->last()->id));
    }

    #[Test]
    public function itDoesntGetOtherBlogsWithDifferentTags(): void
    {
        $blog = $this->build(Blog::class)
            ->hasAttached($this->build(BlogTag::class), relationship: 'tags')
            ->create();

        $otherBlog = $this->build(Blog::class)
            ->hasAttached($this->build(BlogTag::class), relationship: 'tags')
            ->create();

        $relatedBlogs = app(FindRelatedBlogsAction::class)->handle($blog);

        $this->assertEmpty($relatedBlogs);
    }

    #[Test]
    public function itCanLimitTheAmountOfBlogsReturned(): void
    {
        $tag = $this->create(BlogTag::class);

        $blogs = $this->build(Blog::class)
            ->count(10)
            ->hasAttached($tag, relationship: 'tags')
            ->create();

        $relatedBlogs = app(FindRelatedBlogsAction::class)->handle($blogs->first(), 5);

        $this->assertCount(5, $relatedBlogs);
    }

    #[Test]
    public function itWillReturnBlogsFromOtherTagsToGetToTheRequiredCount(): void
    {
        $firstTag = $this->create(BlogTag::class);

        $firstBlogs = $this->build(Blog::class)
            ->count(5)
            ->hasAttached($firstTag, relationship: 'tags')
            ->create();

        $secondTag = $this->create(BlogTag::class);

        $secondBlogs = $this->build(Blog::class)
            ->count(5)
            ->hasAttached($secondTag, relationship: 'tags')
            ->create();

        $blog = $firstBlogs->first();
        $blog->tags()->attach($secondTag);

        $relatedBlogs = app(FindRelatedBlogsAction::class)->handle($blog);

        $this->assertCount(9, $relatedBlogs);
        $this->assertTrue($relatedBlogs->contains('id', $secondBlogs->first()->id));
    }

    #[Test]
    public function itAppendsTheBlogTagAndTagUrlToTheBlogModel(): void
    {
        $tag = $this->create(BlogTag::class);

        $blogs = $this->build(Blog::class)
            ->count(2)
            ->hasAttached($tag, relationship: 'tags')
            ->create();

        $relatedBlogs = app(FindRelatedBlogsAction::class)->handle($blogs->first());

        /** @var Blog $blog */
        $blog = $relatedBlogs->first();

        $this->assertTrue($blog->hasAttribute('related_tag'));
        $this->assertTrue($blog->hasAttribute('related_tag_url'));

        $this->assertEquals($tag->tag, $blog->getAttribute('related_tag'));
        $this->assertEquals(route('blog.index.tags', $tag->slug), $blog->getAttribute('related_tag_url'));
    }

    #[Test]
    public function itMergesMultiplePrimaryTagBlogsWithOtherTagBlogs(): void
    {
        $primaryTag = $this->create(BlogTag::class);
        $otherTag = $this->create(BlogTag::class);

        // Create 3 blogs with the primary tag (less than the default limit of 10)
        $primaryTagBlogs = $this->build(Blog::class)
            ->count(3)
            ->hasAttached($primaryTag, relationship: 'tags')
            ->create();

        // Create blogs with a different tag
        $otherTagBlogs = $this->build(Blog::class)
            ->count(3)
            ->hasAttached($otherTag, relationship: 'tags')
            ->create();

        $blog = $primaryTagBlogs->first();
        $blog->update(['primary_tag_id' => $primaryTag->id]);
        $blog->tags()->attach($otherTag);

        $relatedBlogs = app(FindRelatedBlogsAction::class)->handle($blog);

        // Should have 2 from primary tag + 3 from other tag = 5 total
        $this->assertCount(5, $relatedBlogs);
    }

    #[Test]
    public function itWillDeduplicateBlogsThatShareMultipleTags(): void
    {
        $firstTag = $this->create(BlogTag::class);

        $firstBlogs = $this->build(Blog::class)
            ->count(2)
            ->hasAttached($firstTag, relationship: 'tags')
            ->create();

        $secondTag = $this->create(BlogTag::class);

        $secondBlog = $this->build(Blog::class)
            ->hasAttached($secondTag, relationship: 'tags')
            ->create();

        $blog = $firstBlogs->first();
        $blog->tags()->attach($secondTag);
        $secondBlog->tags()->attach($firstTag);

        $relatedBlogs = app(FindRelatedBlogsAction::class)->handle($blog);

        $this->assertCount(2, $relatedBlogs);
        $this->assertNotNull($relatedBlogs->sole('id', $secondBlog->id));
    }
}
