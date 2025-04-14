<?php

declare(strict_types=1);

namespace Tests\Unit\Feeds;

use App\Feeds\Feed;
use App\Feeds\RecipeFeed;
use App\Models\Recipes\Recipe;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;

class RecipeFeedTest extends FeedTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withRecipes(12);
    }

    protected function callFeed(): Feed
    {
        return new RecipeFeed();
    }

    /** @return Collection<int, Recipe> */
    protected function items(): Collection
    {
        return Recipe::query()->latest()->get();
    }

    #[Test]
    public function itHasATitle(): void
    {
        foreach ($this->items() as $recipe) {
            $this->assertArrayHasKey('title', $this->callFormatItem($recipe));
            $this->assertIsArray($this->callFormatItem($recipe)['title']);
            $this->assertArrayHasKeys(['cdata', 'value'], $this->callFormatItem($recipe)['title']);
            $this->assertTrue($this->callFormatItem($recipe)['title']['cdata']);
            $this->assertEquals($recipe->title, $this->callFormatItem($recipe)['title']['value']);
        }
    }

    #[Test]
    public function itHasALink(): void
    {
        foreach ($this->items() as $recipe) {
            $this->assertArrayHasKey('link', $this->callFormatItem($recipe));
            $this->assertIsArray($this->callFormatItem($recipe)['link']);
            $this->assertArrayHasKey('value', $this->callFormatItem($recipe)['link']);
            $this->assertEquals($recipe->absolute_link, $this->callFormatItem($recipe)['link']['value']);
        }
    }

    #[Test]
    public function itHasADescription(): void
    {
        foreach ($this->items() as $recipe) {
            $this->assertArrayHasKey('description', $this->callFormatItem($recipe));
            $this->assertIsArray($this->callFormatItem($recipe)['description']);
            $this->assertArrayHasKeys(['cdata', 'value'], $this->callFormatItem($recipe)['description']);
            $this->assertTrue($this->callFormatItem($recipe)['description']['cdata']);
            $this->assertEquals($recipe->meta_description, $this->callFormatItem($recipe)['description']['value']);
        }
    }

    #[Test]
    public function itHasAnAuthor(): void
    {
        foreach ($this->items() as $recipe) {
            $this->assertArrayHasKey('author', $this->callFormatItem($recipe));
            $this->assertIsArray($this->callFormatItem($recipe)['author']);
            $this->assertArrayHasKey('value', $this->callFormatItem($recipe)['author']);
            $this->assertEquals('contact@coeliacsanctuary.co.uk (Coeliac Sanctuary)', $this->callFormatItem($recipe)['author']['value']);
        }
    }

    #[Test]
    public function itHasACommentLink(): void
    {
        foreach ($this->items() as $recipe) {
            $this->assertArrayHasKey('comments', $this->callFormatItem($recipe));
            $this->assertIsArray($this->callFormatItem($recipe)['comments']);
            $this->assertArrayHasKey('value', $this->callFormatItem($recipe)['comments']);
            $this->assertEquals($recipe->absolute_link . '#comments', $this->callFormatItem($recipe)['comments']['value']);
        }
    }

    #[Test]
    public function itHasAnEnclosure(): void
    {
        foreach ($this->items() as $recipe) {
            $this->assertArrayHasKey('description', $this->callFormatItem($recipe));
            $this->assertIsArray($this->callFormatItem($recipe)['enclosure']);
            $this->assertArrayHasKeys(['value', 'short', 'params'], $this->callFormatItem($recipe)['enclosure']);
            $this->assertEquals('', $this->callFormatItem($recipe)['enclosure']['value']);
            $this->assertTrue($this->callFormatItem($recipe)['enclosure']['short']);
            $this->assertIsArray($this->callFormatItem($recipe)['enclosure']['params']);
            $this->assertArrayHasKeys(['url', 'type'], $this->callFormatItem($recipe)['enclosure']['params']);
            $this->assertEquals($recipe->main_image, $this->callFormatItem($recipe)['enclosure']['params']['url']);
            $this->assertEquals('image/*', $this->callFormatItem($recipe)['enclosure']['params']['type']);
        }
    }

    #[Test]
    public function itHasAPubDate(): void
    {
        foreach ($this->items() as $recipe) {
            $this->assertArrayHasKey('pubDate', $this->callFormatItem($recipe));
            $this->assertIsArray($this->callFormatItem($recipe)['pubDate']);
            $this->assertArrayHasKey('value', $this->callFormatItem($recipe)['pubDate']);
            $this->assertEquals($recipe->created_at->toRfc822String(), $this->callFormatItem($recipe)['pubDate']['value']);
        }
    }
}
