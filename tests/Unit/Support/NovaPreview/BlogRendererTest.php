<?php

declare(strict_types=1);

namespace Tests\Unit\Support\NovaPreview;

use App\Actions\Blogs\FindRelatedBlogsAction;
use App\Models\Blogs\Blog;
use App\Models\Blogs\BlogTag;
use App\Support\NovaPreview\BlogRenderer;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BlogRendererTest extends TestCase
{
    protected BlogRenderer $renderer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->renderer = app(BlogRenderer::class);
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
    public function itIncludesBodyImagesInThePayload(): void
    {
        $images = [
            ['file_name' => 'img.jpg', 'url' => 'https://example.com/img.jpg'],
        ];

        $result = $this->renderer->payload($this->makePayload(['body_images' => $images]));

        $this->assertEquals($images, $result['blog']['body_images']);
    }

    #[Test]
    public function itDefaultsBodyImagesToAnEmptyArray(): void
    {
        $result = $this->renderer->payload($this->makePayload());

        $this->assertEquals([], $result['blog']['body_images']);
    }

    #[Test]
    public function itReplacesImageFilenamesWithUrlsInTheBody(): void
    {
        $result = $this->renderer->payload($this->makePayload([
            'body' => 'Some text <article-image src="photo.jpg" position="left"></article-image> more text',
            'body_images' => [
                ['file_name' => 'photo.jpg', 'url' => 'https://example.com/storage/photo.jpg'],
            ],
        ]));

        $this->assertStringContainsString('https://example.com/storage/photo.jpg', $result['blog']['body']);
        $this->assertStringNotContainsString('src="photo.jpg"', $result['blog']['body']);
    }

    #[Test]
    public function itSkipsBodyImagesWithNoUrl(): void
    {
        $result = $this->renderer->payload($this->makePayload([
            'body' => 'Some text <article-image src="photo.jpg" position="left"></article-image>',
            'body_images' => [
                ['file_name' => 'photo.jpg', 'url' => null],
            ],
        ]));

        $this->assertStringContainsString('photo.jpg', $result['blog']['body']);
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

    #[Test]
    public function itCalculatesTheReadingTimeFromTheBody(): void
    {
        $result = $this->renderer->payload($this->makePayload([
            'body' => implode(' ', array_fill(0, 600, 'word')),
        ]));

        $this->assertEquals(3, $result['blog']['reading_time']);
    }

    #[Test]
    public function itAlwaysReportsNoComments(): void
    {
        $result = $this->renderer->payload($this->makePayload());

        $this->assertEquals(0, $result['blog']['comments_count']);
    }

    #[Test]
    public function itPublishesAgainstTheCurrentDate(): void
    {
        $this->travelTo(Carbon::parse('2026-08-16 12:00:00'));

        $result = $this->renderer->payload($this->makePayload());

        $this->assertEquals(Carbon::now()->diffForHumans(), $result['blog']['published']);
        $this->assertNull($result['blog']['updated']);
    }

    #[Test]
    public function itMapsTheTagsForDisplay(): void
    {
        $result = $this->renderer->payload($this->makePayload(['tags' => ['Gluten Free', 'Recipes']]));

        $this->assertEquals([
            ['tag' => 'Gluten Free', 'slug' => 'gluten-free'],
            ['tag' => 'Recipes', 'slug' => 'recipes'],
        ], $result['blog']['tags']);
    }

    #[Test]
    public function itReturnsNullForFaqsWhenThereArentAny(): void
    {
        $this->assertNull($this->renderer->payload($this->makePayload())['blog']['faqs']);

        $this->assertNull($this->renderer->payload($this->makePayload(['faqs' => []]))['blog']['faqs']);
    }

    #[Test]
    public function itPassesFaqsThroughWhenPresent(): void
    {
        $faqs = [['question' => 'Is it gluten free?', 'answer' => 'Yes.']];

        $result = $this->renderer->payload($this->makePayload([
            'faqs' => $faqs,
            'faq_display' => 'top',
        ]));

        $this->assertEquals($faqs, $result['blog']['faqs']);
        $this->assertEquals('top', $result['blog']['faq_display']);
    }

    #[Test]
    public function itDropsFaqsWithNoQuestion(): void
    {
        $result = $this->renderer->payload($this->makePayload([
            'faqs' => [
                ['question' => '', 'answer' => 'An orphaned answer.'],
                ['question' => 'A real question?', 'answer' => 'A real answer.'],
            ],
        ]));

        $this->assertCount(1, $result['blog']['faqs']);
        $this->assertEquals('A real question?', $result['blog']['faqs'][0]['question']);
    }

    #[Test]
    public function itFindsRelatedBlogsForTheGivenTags(): void
    {
        $tag = $this->create(BlogTag::class, ['tag' => 'Gluten Free']);

        $blog = $this->build(Blog::class)->hasAttached($tag, relationship: 'tags')->create();

        $result = $this->renderer->payload($this->makePayload(['tags' => ['Gluten Free']]));

        $this->assertCount(1, $result['relatedBlogs']);
        $this->assertEquals($blog->id, $result['relatedBlogs']->first()->id);
    }

    #[Test]
    public function itPassesTheResolvedPrimaryTagToTheRelatedBlogsAction(): void
    {
        $primaryTag = $this->create(BlogTag::class, ['tag' => 'Gluten Free']);
        $otherTag = $this->create(BlogTag::class, ['tag' => 'Recipes']);

        $this->expectAction(FindRelatedBlogsAction::class, [
            fn (Collection $tags, ?BlogTag $resolved): bool => $resolved?->id === $primaryTag->id && $tags->count() === 2,
        ], return: collect());

        app(BlogRenderer::class)->payload($this->makePayload([
            'tags' => [$primaryTag->tag, $otherTag->tag],
            'primary_tag_id' => 'Gluten Free',
        ]));
    }

    #[Test]
    public function itIgnoresTagsThatDontExist(): void
    {
        $result = $this->renderer->payload($this->makePayload(['tags' => ['Not A Real Tag']]));

        $this->assertEquals([['tag' => 'Not A Real Tag', 'slug' => 'not-a-real-tag']], $result['blog']['tags']);
        $this->assertCount(0, $result['relatedBlogs']);
    }
}
