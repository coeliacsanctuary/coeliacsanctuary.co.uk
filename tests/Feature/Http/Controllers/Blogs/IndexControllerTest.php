<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Blogs;

use App\Actions\Blogs\GetBlogsForBlogIndexAction;
use App\Actions\Blogs\GetBlogTagsAction;
use App\Actions\OpenGraphImages\GetOpenGraphImageForRouteAction;
use App\Models\Blogs\Blog;
use App\Models\Blogs\BlogTag;
use Illuminate\Support\Facades\Storage;
use Inertia\Support\Header;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    protected BlogTag $tag;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('media');

        $this->withBlogs(30);

        $this->tag = $this->create(BlogTag::class);

        Blog::query()->latest()->take(2)->get()->each(function (Blog $blog): void {
            $blog->tags()->attach($this->tag);
        });
    }

    #[Test]
    public function itLoadsTheBlogListPage(): void
    {
        $this->get(route('blog.index'))->assertOk();
    }

    #[Test]
    public function itCallsTheGetBlogsForIndexAction(): void
    {
        $this->expectAction(GetBlogsForBlogIndexAction::class)
            ->get(route('blog.index'));

        $tag = BlogTag::query()->first();

        $this->expectAction(GetBlogsForBlogIndexAction::class, [BlogTag::class])
            ->get(route('blog.index.tags', ['tag' => $tag->slug]));
    }

    #[Test]
    public function itCallsTheGetOpenGraphImageForRouteAction(): void
    {
        $this->expectAction(GetOpenGraphImageForRouteAction::class, ['blog']);

        $this->get(route('blog.index'));
    }

    #[Test]
    public function itReturnsTheFirst12Blogs(): void
    {
        $this->get(route('blog.index'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Blog/Index')
                    ->has('blogs')
                    ->has(
                        'blogs.data',
                        12,
                        fn (Assert $page) => $page
                            ->hasAll(['title', 'description', 'date', 'image', 'link', 'description', 'tags', 'comments_count'])
                    )
                    ->where('blogs.data.0.title', 'Blog 0')
                    ->where('blogs.data.1.title', 'Blog 1')
                    ->has('blogs.links')
                    ->has('blogs.meta')
                    ->where('blogs.meta.current_page', 1)
                    ->where('blogs.meta.per_page', 12)
                    ->where('blogs.meta.total', 30)
                    ->etc()
            );
    }

    #[Test]
    public function itCanLoadOtherPages(): void
    {
        $this->get(route('blog.index', ['page' => 2]))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Blog/Index')
                    ->has('blogs')
                    ->has(
                        'blogs.data',
                        12,
                        fn (Assert $page) => $page
                            ->hasAll(['title', 'description', 'date', 'image', 'link', 'description', 'tags', 'comments_count'])
                    )
                    ->where('blogs.data.0.title', 'Blog 12')
                    ->where('blogs.data.1.title', 'Blog 13')
                    ->has('blogs.links')
                    ->has('blogs.meta')
                    ->where('blogs.meta.current_page', 2)
                    ->where('blogs.meta.per_page', 12)
                    ->where('blogs.meta.total', 30)
                    ->etc()
            );
    }

    #[Test]
    public function itDoesntHaveTheBlogTagsByDefault(): void
    {
        $this->get(route('blog.index'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Blog/Index')
                    ->missing('tags')
            );
    }

    #[Test]
    public function itHasTheTagsInADeferredResponse(): void
    {
        $this->get(route('blog.index'), [
            Header::PARTIAL_ONLY => 'tags',
            Header::PARTIAL_COMPONENT => 'Blog/Index',
        ])
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Blog/Index')
                    ->has('tags', BlogTag::count(), fn (Assert $page) => $page->hasAll(['slug', 'tag', 'blogs_count'])->etc())
                    ->where('tags.0.tag', $this->tag->tag)
                    ->where('tags.0.slug', $this->tag->slug)
                    ->where('tags.0.blogs_count', 2)
                    ->etc()
            );
    }

    #[Test]
    public function itLoadsTheBlogListPageWhenATagIsInTheUrl(): void
    {
        $this->get(route('blog.index.tags', ['tag' => $this->tag->slug]))->assertOk();
    }

    #[Test]
    public function itCallsTheGetBlogTagsActionWhenRequestingTags(): void
    {
        $tag = BlogTag::query()->first();

        $this->expectAction(GetBlogTagsAction::class)
            ->get(route('blog.index.tags', ['tag' => $tag->slug]), [
                Header::PARTIAL_ONLY => 'tags',
                Header::PARTIAL_COMPONENT => 'Blog/Index',
            ]);
    }

    #[Test]
    public function itOnlyReturnsBlogsWithTheGivenTag(): void
    {
        $this->get(route('blog.index.tags', ['tag' => $this->tag->slug]))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Blog/Index')
                    ->has('blogs')
                    ->has('blogs.data', 2, fn (Assert $page) => $page->hasAll(['title', 'description', 'date', 'image', 'link', 'description', 'tags', 'comments_count']))
                    ->where('blogs.data.0.title', 'Blog 0')
                    ->has('blogs.links')
                    ->has('blogs.meta')
                    ->where('blogs.meta.current_page', 1)
                    ->where('blogs.meta.per_page', 12)
                    ->where('blogs.meta.total', 2)
                    ->etc()
            );
    }

    #[Test]
    public function itCanLoadOtherPagesForTheGivenTag(): void
    {
        $this->tag = $this->create(BlogTag::class);

        Blog::query()->get()->each(function (Blog $blog): void {
            $blog->tags()->attach($this->tag);
        });

        $this->get(route('blog.index.tags', ['tag' => $this->tag->slug, 'page' => 2]))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Blog/Index')
                    ->has('blogs')
                    ->has(
                        'blogs.data',
                        12,
                        fn (Assert $page) => $page
                            ->hasAll(['title', 'description', 'date', 'image', 'link', 'description', 'tags', 'comments_count'])
                    )
                    ->where('blogs.data.0.title', 'Blog 12')
                    ->where('blogs.data.1.title', 'Blog 13')
                    ->has('blogs.links')
                    ->has('blogs.meta')
                    ->where('blogs.meta.current_page', 2)
                    ->where('blogs.meta.per_page', 12)
                    ->where('blogs.meta.total', 30)
                    ->etc()
            );
    }
}
