<?php

declare(strict_types=1);

namespace Tests\Unit\Resources\Blogs;

use App\Models\Blogs\Blog;
use App\Models\Comments\Comment;
use App\Models\Faqs\Faq;
use App\Resources\Blogs\BlogShowResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BlogShowResourceTest extends TestCase
{
    protected Blog $blog;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withBlogs(1);

        $this->blog = Blog::query()->first();
    }

    #[Test]
    public function itReturnsNullFaqsWhenThereAreNoFaqs(): void
    {
        $resource = (new BlogShowResource($this->blog))->toArray(new Request());

        $this->assertNull($resource['faqs']);
    }

    #[Test]
    public function itReturnsFaqsFromTheRelation(): void
    {
        $this->build(Faq::class)->on($this->blog)->create(['question' => 'Is this gluten free?', 'answer' => 'Yes!']);
        $this->build(Faq::class)->on($this->blog)->create(['question' => 'Can I freeze it?', 'answer' => 'Absolutely.']);

        $resource = (new BlogShowResource($this->blog->fresh()))->toArray(new Request());

        $this->assertCount(2, $resource['faqs']);
        $this->assertSame('Is this gluten free?', $resource['faqs'][0]['question']);
        $this->assertSame('Yes!', $resource['faqs'][0]['answer']);
        $this->assertSame('Can I freeze it?', $resource['faqs'][1]['question']);
        $this->assertSame('Absolutely.', $resource['faqs'][1]['answer']);
    }

    #[Test]
    public function itReturnsShortTitle(): void
    {
        $this->blog->update(['short_title' => 'My short title']);

        $resource = (new BlogShowResource($this->blog->fresh()))->toArray(new Request());

        $this->assertSame('My short title', $resource['short_title']);
    }

    #[Test]
    public function itReturnsNullShortTitleWhenNotSet(): void
    {
        $resource = (new BlogShowResource($this->blog))->toArray(new Request());

        $this->assertNull($resource['short_title']);
    }

    #[Test]
    public function itReturnsFaqDisplay(): void
    {
        $this->blog->update(['faq_display' => 'top']);

        $resource = (new BlogShowResource($this->blog->fresh()))->toArray(new Request());

        $this->assertSame('top', $resource['faq_display']);
    }

    #[Test]
    public function itReturnsNullFaqDisplayWhenNotSet(): void
    {
        $resource = (new BlogShowResource($this->blog))->toArray(new Request());

        $this->assertNull($resource['faq_display']);
    }

    #[Test]
    public function itReturnsNullUpdatedForABlogThatHasNeverBeenEdited(): void
    {
        $resource = (new BlogShowResource($this->blog))->toArray(new Request());

        $this->assertNull($resource['updated']);
    }

    #[Test]
    public function itReturnsUpdatedForABlogThatHasBeenEdited(): void
    {
        $this->blog->update(['updated_at' => Carbon::now()->addYear()]);

        $resource = (new BlogShowResource($this->blog->fresh()))->toArray(new Request());

        $this->assertNotNull($resource['updated']);
        $this->assertNotSame($resource['published'], $resource['updated']);
    }

    #[Test]
    public function itReturnsTheReadingTimeInWholeMinutes(): void
    {
        $this->blog->update(['body' => implode(' ', array_fill(0, 450, 'word'))]);

        $resource = (new BlogShowResource($this->blog->fresh()))->toArray(new Request());

        $this->assertSame(3, $resource['reading_time']);
    }

    #[Test]
    public function itOnlyCountsApprovedComments(): void
    {
        $this->build(Comment::class)->on($this->blog)->approved()->create();
        $this->build(Comment::class)->on($this->blog)->create();

        $resource = (new BlogShowResource($this->blog->fresh()))->toArray(new Request());

        $this->assertSame(1, $resource['comments_count']);
    }

    #[Test]
    public function itReturnsAtLeastAMinuteOfReadingTimeForAShortBlog(): void
    {
        $this->blog->update(['body' => 'Short.']);

        $resource = (new BlogShowResource($this->blog->fresh()))->toArray(new Request());

        $this->assertSame(1, $resource['reading_time']);
    }

    #[Test]
    public function itDoesntCountMarkupTowardsTheReadingTime(): void
    {
        $this->blog->update([
            'body' => '<article-header>Heading</article-header><article-image src="foo.jpg" /> word word',
        ]);

        $resource = (new BlogShowResource($this->blog->fresh()))->toArray(new Request());

        $this->assertSame(1, $resource['reading_time']);
    }

    #[Test]
    public function itStripsTheTwitterScriptFromTheBodyAndFlagsTheEmbed(): void
    {
        $script = '<script async src="https://platform.twitter.com/widgets.js" charset="utf-8"></script>';

        $this->blog->update(['body' => "Some words. {$script}"]);

        $resource = (new BlogShowResource($this->blog->fresh()))->toArray(new Request());

        $this->assertTrue($resource['hasTwitterEmbed']);
        $this->assertStringNotContainsString('platform.twitter.com', (string) $resource['body']);
        $this->assertStringContainsString('Some words.', (string) $resource['body']);
    }
}
