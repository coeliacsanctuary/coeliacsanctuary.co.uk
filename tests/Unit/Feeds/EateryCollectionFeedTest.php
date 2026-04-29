<?php

declare(strict_types=1);

namespace Tests\Unit\Feeds;

use App\Feeds\EateryCollectionFeed;
use App\Feeds\Feed;
use App\Models\EatingOut\EateryCollection;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;

class EateryCollectionFeedTest extends FeedTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->markTestSkipped('feature disabled');

        $this->withEateryCollections(12);
    }

    protected function callFeed(): Feed
    {
        return new EateryCollectionFeed();
    }

    /** @return Collection<int, EateryCollection> */
    protected function items(): Collection
    {
        return EateryCollection::query()->latest()->get();
    }

    #[Test]
    public function itHasATitle(): void
    {
        foreach ($this->items() as $collection) {
            $this->assertArrayHasKey('title', $this->callFormatItem($collection));
            $this->assertIsArray($this->callFormatItem($collection)['title']);
            $this->assertArrayHasKeys(['cdata', 'value'], $this->callFormatItem($collection)['title']);
            $this->assertTrue($this->callFormatItem($collection)['title']['cdata']);
            $this->assertEquals($collection->title, $this->callFormatItem($collection)['title']['value']);
        }
    }

    #[Test]
    public function itHasALink(): void
    {
        foreach ($this->items() as $collection) {
            $this->assertArrayHasKey('link', $this->callFormatItem($collection));
            $this->assertIsArray($this->callFormatItem($collection)['link']);
            $this->assertArrayHasKey('value', $this->callFormatItem($collection)['link']);
            $this->assertEquals($collection->absolute_link, $this->callFormatItem($collection)['link']['value']);
        }
    }

    #[Test]
    public function itHasADescription(): void
    {
        foreach ($this->items() as $collection) {
            $this->assertArrayHasKey('description', $this->callFormatItem($collection));
            $this->assertIsArray($this->callFormatItem($collection)['description']);
            $this->assertArrayHasKeys(['cdata', 'value'], $this->callFormatItem($collection)['description']);
            $this->assertTrue($this->callFormatItem($collection)['description']['cdata']);
            $this->assertEquals($collection->meta_description, $this->callFormatItem($collection)['description']['value']);
        }
    }

    #[Test]
    public function itHasAnAuthor(): void
    {
        foreach ($this->items() as $collection) {
            $this->assertArrayHasKey('author', $this->callFormatItem($collection));
            $this->assertIsArray($this->callFormatItem($collection)['author']);
            $this->assertArrayHasKey('value', $this->callFormatItem($collection)['author']);
            $this->assertEquals('contact@coeliacsanctuary.co.uk (Coeliac Sanctuary)', $this->callFormatItem($collection)['author']['value']);
        }
    }

    #[Test]
    public function itHasAnEnclosure(): void
    {
        foreach ($this->items() as $collection) {
            $this->assertArrayHasKey('description', $this->callFormatItem($collection));
            $this->assertIsArray($this->callFormatItem($collection)['enclosure']);
            $this->assertArrayHasKeys(['value', 'short', 'params'], $this->callFormatItem($collection)['enclosure']);
            $this->assertEquals('', $this->callFormatItem($collection)['enclosure']['value']);
            $this->assertTrue($this->callFormatItem($collection)['enclosure']['short']);
            $this->assertIsArray($this->callFormatItem($collection)['enclosure']['params']);
            $this->assertArrayHasKeys(['url', 'type'], $this->callFormatItem($collection)['enclosure']['params']);
            $this->assertEquals($collection->main_image, $this->callFormatItem($collection)['enclosure']['params']['url']);
            $this->assertEquals('image/*', $this->callFormatItem($collection)['enclosure']['params']['type']);
        }
    }

    #[Test]
    public function itHasAPubDate(): void
    {
        foreach ($this->items() as $collection) {
            $this->assertArrayHasKey('pubDate', $this->callFormatItem($collection));
            $this->assertIsArray($this->callFormatItem($collection)['pubDate']);
            $this->assertArrayHasKey('value', $this->callFormatItem($collection)['pubDate']);
            $this->assertEquals($collection->created_at->toRfc822String(), $this->callFormatItem($collection)['pubDate']['value']);
        }
    }
}
