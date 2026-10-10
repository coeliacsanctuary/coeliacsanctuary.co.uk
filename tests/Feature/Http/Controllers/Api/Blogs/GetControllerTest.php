<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Blogs;

use PHPUnit\Framework\Attributes\Test;
use App\Models\Blogs\Blog;
use Tests\TestCase;

class GetControllerTest extends TestCase
{
    #[Test]
    public function itErrorsIfABlogCantBeFound(): void
    {
        $this->get(route('api.blogs.show', 'foo'))->assertNotFound();
    }

    #[Test]
    public function itErrorsIfABlogIsntLive(): void
    {
        $blog = $this->build(Blog::class)->notLive()->create();

        $this->get(route('api.blogs.show', $blog))->assertNotFound();
    }

    #[Test]
    public function itErrorsIfABlogIsDraft(): void
    {
        $blog = $this->build(Blog::class)->draft()->create();

        $this->get(route('api.blogs.show', $blog))->assertNotFound();
    }

    #[Test]
    public function itReturnsTheBlog(): void
    {
        $this->withBlogs(1);

        $blog = Blog::query()->first();

        $this->get(route('api.blogs.show', $blog))->assertOk();
    }
}
