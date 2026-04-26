<?php

declare(strict_types=1);

namespace Tests\Unit\Support\NovaPreview;

use App\Support\NovaPreview\BlogRenderer;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BlogRendererTest extends TestCase
{
    protected BlogRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->renderer = new BlogRenderer();
    }

    protected function makePayload(array $overrides = []): array
    {
        return array_merge([
            'title' => 'My Blog Title',
            'description' => 'A description.',
            'body' => '<p>Some body content.</p>',
            'primary_image_url' => 'https://example.com/image.jpg',
            'show_author' => true,
        ], $overrides);
    }

    #[Test]
    public function itReturnsTheBlogPreviewComponent(): void
    {
        $this->assertEquals('Blog/Preview', $this->renderer->component());
    }

    #[Test]
    public function itBuildsTheBlogPayloadStructure(): void
    {
        $result = $this->renderer->payload($this->makePayload());

        $this->assertArrayHasKey('blog', $result);
        $this->assertEquals(0, $result['blog']['id']);
        $this->assertEquals('My Blog Title', $result['blog']['title']);
        $this->assertEquals('A description.', $result['blog']['description']);
        $this->assertEquals('https://example.com/image.jpg', $result['blog']['image']);
        $this->assertNull($result['blog']['updated']);
        $this->assertEquals([], $result['blog']['tags']);
        $this->assertEquals([], $result['blog']['featured_in']);
    }

    #[Test]
    public function itRendersBodyAsMarkdown(): void
    {
        $result = $this->renderer->payload($this->makePayload(['body' => '**bold text**']));

        $this->assertStringContainsString('<strong>bold text</strong>', $result['blog']['body']);
    }

    #[Test]
    public function itDecodesHtmlEntitiesInTitle(): void
    {
        $result = $this->renderer->payload($this->makePayload(['title' => 'Fish &amp; Chips or Fish &quot;Chips&quot;']));

        $this->assertStringContainsString('"Chips"', $result['blog']['title']);
    }

    #[Test]
    public function itDefaultsShowAuthorToTrueWhenMissing(): void
    {
        $result = $this->renderer->payload($this->makePayload(['show_author' => null]));

        $this->assertTrue($result['blog']['show_author']);
    }

    #[Test]
    public function itRespectsShowAuthorFalse(): void
    {
        $result = $this->renderer->payload($this->makePayload(['show_author' => false]));

        $this->assertFalse($result['blog']['show_author']);
    }

    #[Test]
    public function itDetectsTwitterEmbeds(): void
    {
        $body = 'Content <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>';

        $result = $this->renderer->payload($this->makePayload(['body' => $body]));

        $this->assertTrue($result['blog']['hasTwitterEmbed']);
    }

    #[Test]
    public function itReturnsFalseForHasTwitterEmbedWhenNonePresent(): void
    {
        $result = $this->renderer->payload($this->makePayload(['body' => '<p>No twitter here.</p>']));

        $this->assertFalse($result['blog']['hasTwitterEmbed']);
    }

    #[Test]
    public function itStripsTwitterEmbedScriptsFromBody(): void
    {
        $body = 'Content <script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script> after';

        $result = $this->renderer->payload($this->makePayload(['body' => $body]));

        $this->assertStringNotContainsString('widgets.js', $result['blog']['body']);
    }
}
