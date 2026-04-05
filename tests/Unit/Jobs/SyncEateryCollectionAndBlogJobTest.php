<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs;

use App\Jobs\SyncEateryCollectionAndBlogJob;
use App\Models\Blogs\Blog;
use App\Models\Blogs\BlogTag;
use App\Models\EatingOut\EateryCollection;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SyncEateryCollectionAndBlogJobTest extends TestCase
{
    protected EateryCollection $collection;

    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        $this->collection = $this->create(EateryCollection::class, [
            'cross_post_to_blogs' => true,
            'blog_id' => null,
        ], quietly: true);
    }

    #[Test]
    public function itCreatesABlogFromTheCollection(): void
    {
        $this->assertDatabaseEmpty(Blog::class);

        (new SyncEateryCollectionAndBlogJob($this->collection))->handle();

        $this->assertDatabaseCount(Blog::class, 1);

        $blog = Blog::query()->first();

        $this->assertEquals($this->collection->id, $blog->eatery_collection_id);
        $this->assertEquals($this->collection->title, $blog->title);
        $this->assertEquals($this->collection->slug, $blog->slug);
    }

    #[Test]
    public function itUpdatesAnExistingBlogWhenOneAlreadyExists(): void
    {
        $blog = $this->create(Blog::class, ['eatery_collection_id' => $this->collection->id]);

        (new SyncEateryCollectionAndBlogJob($this->collection))->handle();

        $this->assertDatabaseCount(Blog::class, 1);

        $blog->refresh();

        $this->assertEquals($this->collection->id, $blog->eatery_collection_id);
        $this->assertEquals($this->collection->title, $blog->title);
    }

    #[Test]
    public function itSetsTheBlogIdOnTheCollection(): void
    {
        $this->assertNull($this->collection->blog_id);

        (new SyncEateryCollectionAndBlogJob($this->collection))->handle();

        $blog = Blog::query()->where('eatery_collection_id', $this->collection->id)->first();

        $this->assertEquals($blog->id, $this->collection->fresh()->blog_id);
    }

    #[Test]
    public function itCreatesTheEateryCollectionBlogTag(): void
    {
        $this->assertDatabaseEmpty(BlogTag::class);

        (new SyncEateryCollectionAndBlogJob($this->collection))->handle();

        $this->assertDatabaseCount(BlogTag::class, 1);

        $tag = BlogTag::query()->first();

        $this->assertEquals('Eatery Collection', $tag->tag);
        $this->assertEquals('eatery-collection', $tag->slug);
    }

    #[Test]
    public function itReusesAnExistingEateryCollectionBlogTag(): void
    {
        $tag = $this->create(BlogTag::class, ['tag' => 'Eatery Collection', 'slug' => 'eatery-collection']);

        (new SyncEateryCollectionAndBlogJob($this->collection))->handle();

        $this->assertDatabaseCount(BlogTag::class, 1);

        $blog = Blog::query()->where('eatery_collection_id', $this->collection->id)->first();

        $this->assertTrue($blog->tags->contains($tag));
    }

    #[Test]
    public function itAssignsTheEateryCollectionTagToTheBlog(): void
    {
        (new SyncEateryCollectionAndBlogJob($this->collection))->handle();

        $blog = Blog::query()->where('eatery_collection_id', $this->collection->id)->first();

        $this->assertCount(1, $blog->tags);
        $this->assertEquals('eatery-collection', $blog->tags->first()->slug);
    }

    #[Test]
    public function itDeletesTheBlogWhenCrossPostingIsDisabled(): void
    {
        $blog = $this->create(Blog::class, ['eatery_collection_id' => $this->collection->id]);

        $this->collection->update(['cross_post_to_blogs' => false, 'blog_id' => $blog->id]);

        (new SyncEateryCollectionAndBlogJob($this->collection->fresh()))->handle();

        $this->assertDatabaseCount(Blog::class, 0);
    }

    #[Test]
    public function itClearsTheBlogIdOnTheCollectionWhenTheBlogIsDeleted(): void
    {
        $blog = $this->create(Blog::class, ['eatery_collection_id' => $this->collection->id]);

        $this->collection->update(['cross_post_to_blogs' => false, 'blog_id' => $blog->id]);

        (new SyncEateryCollectionAndBlogJob($this->collection->fresh()))->handle();

        $this->assertNull($this->collection->fresh()->blog_id);
    }

    #[Test]
    public function itDoesNothingWhenCrossPostingIsDisabledAndNoBlogExists(): void
    {
        $this->collection->update(['cross_post_to_blogs' => false]);

        (new SyncEateryCollectionAndBlogJob($this->collection->fresh()))->handle();

        $this->assertDatabaseCount(Blog::class, 0);
    }
}
