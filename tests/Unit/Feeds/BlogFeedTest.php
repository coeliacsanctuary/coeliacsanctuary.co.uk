<?php

declare(strict_types=1);

namespace Tests\Unit\Feeds;

use App\Feeds\BlogFeed;
use App\Feeds\Feed;
use App\Models\Blogs\Blog;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;

class BlogFeedTest extends FeedTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withBlogs(12);
    }

    protected function callFeed(): Feed
    {
        return new BlogFeed();
    }

    /** @return Collection<int, Blog> */
    protected function items(): Collection
    {
        return Blog::query()->latest()->get();
    }

    #[Test]
    public function itHasATitle(): void
    {
        foreach ($this->items() as $blog) {
            $this->assertArrayHasKey('title', $this->callFormatItem($blog));
            $this->assertIsArray($this->callFormatItem($blog)['title']);
            $this->assertArrayHasKeys(['cdata', 'value'], $this->callFormatItem($blog)['title']);
            $this->assertTrue($this->callFormatItem($blog)['title']['cdata']);
            $this->assertEquals($blog->title, $this->callFormatItem($blog)['title']['value']);
        }
    }

    #[Test]
    public function itHasALink(): void
    {
        foreach ($this->items() as $blog) {
            $this->assertArrayHasKey('link', $this->callFormatItem($blog));
            $this->assertIsArray($this->callFormatItem($blog)['link']);
            $this->assertArrayHasKey('value', $this->callFormatItem($blog)['link']);
            $this->assertEquals($blog->absolute_link, $this->callFormatItem($blog)['link']['value']);
        }
    }

    #[Test]
    public function itHasADescription(): void
    {
        foreach ($this->items() as $blog) {
            $this->assertArrayHasKey('description', $this->callFormatItem($blog));
            $this->assertIsArray($this->callFormatItem($blog)['description']);
            $this->assertArrayHasKeys(['cdata', 'value'], $this->callFormatItem($blog)['description']);
            $this->assertTrue($this->callFormatItem($blog)['description']['cdata']);
            $this->assertEquals($blog->meta_description, $this->callFormatItem($blog)['description']['value']);
        }
    }

    #[Test]
    public function itHasAnAuthor(): void
    {
        foreach ($this->items() as $blog) {
            $this->assertArrayHasKey('author', $this->callFormatItem($blog));
            $this->assertIsArray($this->callFormatItem($blog)['author']);
            $this->assertArrayHasKey('value', $this->callFormatItem($blog)['author']);
            $this->assertEquals('contact@coeliacsanctuary.co.uk (Coeliac Sanctuary)', $this->callFormatItem($blog)['author']['value']);
        }
    }

    #[Test]
    public function itHasACommentLink(): void
    {
        foreach ($this->items() as $blog) {
            $this->assertArrayHasKey('comments', $this->callFormatItem($blog));
            $this->assertIsArray($this->callFormatItem($blog)['comments']);
            $this->assertArrayHasKey('value', $this->callFormatItem($blog)['comments']);
            $this->assertEquals($blog->absolute_link . '#comments', $this->callFormatItem($blog)['comments']['value']);
        }
    }

    #[Test]
    public function itHasAnEnclosure(): void
    {
        foreach ($this->items() as $blog) {
            $this->assertArrayHasKey('description', $this->callFormatItem($blog));
            $this->assertIsArray($this->callFormatItem($blog)['enclosure']);
            $this->assertArrayHasKeys(['value', 'short', 'params'], $this->callFormatItem($blog)['enclosure']);
            $this->assertEquals('', $this->callFormatItem($blog)['enclosure']['value']);
            $this->assertTrue($this->callFormatItem($blog)['enclosure']['short']);
            $this->assertIsArray($this->callFormatItem($blog)['enclosure']['params']);
            $this->assertArrayHasKeys(['url', 'type'], $this->callFormatItem($blog)['enclosure']['params']);
            $this->assertEquals($blog->main_image, $this->callFormatItem($blog)['enclosure']['params']['url']);
            $this->assertEquals('image/*', $this->callFormatItem($blog)['enclosure']['params']['type']);
        }
    }

    #[Test]
    public function itHasAPubDate(): void
    {
        foreach ($this->items() as $blog) {
            $this->assertArrayHasKey('pubDate', $this->callFormatItem($blog));
            $this->assertIsArray($this->callFormatItem($blog)['pubDate']);
            $this->assertArrayHasKey('value', $this->callFormatItem($blog)['pubDate']);
            $this->assertEquals($blog->created_at->toRfc822String(), $this->callFormatItem($blog)['pubDate']['value']);
        }
    }
}
