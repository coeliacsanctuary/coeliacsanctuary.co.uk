<?php

declare(strict_types=1);

namespace Tests\Unit\Feeds;

use App\Feeds\CombinedFeed;
use App\Feeds\Feed;
use App\Models\Blogs\Blog;
use App\Models\Recipes\Recipe;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;

class CombinedFeedTest extends FeedTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withBlogs(5);
        $this->withRecipes(5);
    }

    protected function callFeed(): Feed
    {
        return new CombinedFeed();
    }

    /** @return Collection<int, Blog | Recipe> */
    protected function items(): Collection
    {
        return collect([...Blog::query()->latest()->get(), ...Recipe::query()->latest()->get()]);
    }

    #[Test]
    public function itHasATitle(): void
    {
        foreach ($this->items() as $item) {
            $this->assertArrayHasKey('title', $this->callFormatItem($item));
            $this->assertIsArray($this->callFormatItem($item)['title']);
            $this->assertArrayHasKeys(['cdata', 'value'], $this->callFormatItem($item)['title']);
            $this->assertTrue($this->callFormatItem($item)['title']['cdata']);
            $this->assertEquals($item->title, $this->callFormatItem($item)['title']['value']);
        }
    }

    #[Test]
    public function itHasALink(): void
    {
        foreach ($this->items() as $item) {
            $this->assertArrayHasKey('link', $this->callFormatItem($item));
            $this->assertIsArray($this->callFormatItem($item)['link']);
            $this->assertArrayHasKey('value', $this->callFormatItem($item)['link']);
            $this->assertEquals($item->absolute_link, $this->callFormatItem($item)['link']['value']);
        }
    }

    #[Test]
    public function itHasADescription(): void
    {
        foreach ($this->items() as $item) {
            $this->assertArrayHasKey('description', $this->callFormatItem($item));
            $this->assertIsArray($this->callFormatItem($item)['description']);
            $this->assertArrayHasKeys(['cdata', 'value'], $this->callFormatItem($item)['description']);
            $this->assertTrue($this->callFormatItem($item)['description']['cdata']);
            $this->assertEquals($item->meta_description, $this->callFormatItem($item)['description']['value']);
        }
    }

    #[Test]
    public function itHasAnAuthor(): void
    {
        foreach ($this->items() as $item) {
            $this->assertArrayHasKey('author', $this->callFormatItem($item));
            $this->assertIsArray($this->callFormatItem($item)['author']);
            $this->assertArrayHasKey('value', $this->callFormatItem($item)['author']);
            $this->assertEquals('contact@coeliacsanctuary.co.uk (Coeliac Sanctuary)', $this->callFormatItem($item)['author']['value']);
        }
    }

    #[Test]
    public function itHasACommentLink(): void
    {
        foreach ($this->items() as $item) {
            $this->assertArrayHasKey('comments', $this->callFormatItem($item));
            $this->assertIsArray($this->callFormatItem($item)['comments']);
            $this->assertArrayHasKey('value', $this->callFormatItem($item)['comments']);
            $this->assertEquals($item->absolute_link . '#comments', $this->callFormatItem($item)['comments']['value']);
        }
    }

    #[Test]
    public function itHasAnEnclosure(): void
    {
        foreach ($this->items() as $item) {
            $this->assertArrayHasKey('description', $this->callFormatItem($item));
            $this->assertIsArray($this->callFormatItem($item)['enclosure']);
            $this->assertArrayHasKeys(['value', 'short', 'params'], $this->callFormatItem($item)['enclosure']);
            $this->assertEquals('', $this->callFormatItem($item)['enclosure']['value']);
            $this->assertTrue($this->callFormatItem($item)['enclosure']['short']);
            $this->assertIsArray($this->callFormatItem($item)['enclosure']['params']);
            $this->assertArrayHasKeys(['url', 'type'], $this->callFormatItem($item)['enclosure']['params']);
            $this->assertEquals($item->main_image, $this->callFormatItem($item)['enclosure']['params']['url']);
            $this->assertEquals('image/*', $this->callFormatItem($item)['enclosure']['params']['type']);
        }
    }

    #[Test]
    public function itHasAPubDate(): void
    {
        foreach ($this->items() as $item) {
            $this->assertArrayHasKey('pubDate', $this->callFormatItem($item));
            $this->assertIsArray($this->callFormatItem($item)['pubDate']);
            $this->assertArrayHasKey('value', $this->callFormatItem($item)['pubDate']);
            $this->assertEquals($item->created_at->toRfc822String(), $this->callFormatItem($item)['pubDate']['value']);
        }
    }
}
