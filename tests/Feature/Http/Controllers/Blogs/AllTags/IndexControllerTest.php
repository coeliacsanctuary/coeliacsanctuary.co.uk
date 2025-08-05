<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Blogs\AllTags;

use App\Models\Blogs\Blog;
use App\Models\Blogs\BlogTag;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->build(BlogTag::class)
            ->count(20)
            ->create()
            ->each(function (BlogTag $tag): void {
                $blogs = $this->build(Blog::class)
                    ->count(random_int(5, 20))
                    ->create();

                $tag->blogs()->attach($blogs);
            });
    }

    #[Test]
    public function itLoadsTheBlogAllTagsPage(): void
    {
        $this->get(route('blog.tags'))->assertOk();
    }

    #[Test]
    public function itIncludesTheTagsInTheInertiaResponse(): void
    {
        $this->get(route('blog.tags'))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Blog/AllTags')
                    ->has('tags')
                    ->has('tags.0.group')
                    ->has('tags.0.tags')
                    ->has('tags.0.tags.0.tag')
                    ->has('tags.0.tags.0.blogs')
                    ->has('tags.0.tags.0.link')
            );
    }
}
