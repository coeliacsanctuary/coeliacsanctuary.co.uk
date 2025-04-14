<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Blogs\Feed;

use App\Feeds\BlogFeed;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->withBlogs(12);
    }

    #[Test]
    public function itReturnsOk(): void
    {
        $this->get(route('blog.feed'))->assertOk();
    }

    #[Test]
    public function itCallsTheBlogFeedClass(): void
    {
        $this->mock(BlogFeed::class)
            ->shouldReceive('render')
            ->andReturn(view('static.feed'))
            ->once();

        $this->get(route('blog.feed'));
    }

    #[Test]
    public function itReturnsAnXmlHeader(): void
    {
        $this->get(route('blog.feed'))->assertHeader('Content-Type', 'text/xml; charset=UTF-8');
    }

    #[Test]
    public function itReturnsTheFeedView(): void
    {
        $this->get(route('blog.feed'))->assertViewIs('static.feed');
    }
}
