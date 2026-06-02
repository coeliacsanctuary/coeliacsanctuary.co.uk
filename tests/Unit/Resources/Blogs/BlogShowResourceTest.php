<?php

declare(strict_types=1);

namespace Tests\Unit\Resources\Blogs;

use App\Models\Blogs\Blog;
use App\Resources\Blogs\BlogShowResource;
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
    public function itReturnsNullFaqsWhenFaqsColumnIsNull(): void
    {
        $resource = (new BlogShowResource($this->blog))->toArray(new Request());

        $this->assertNull($resource['faqs']);
    }

    #[Test]
    public function itParsesFaqsWhenSet(): void
    {
        $this->blog->update([
            'faqs' => [
                ['fields' => ['question' => 'Is this gluten free?', 'answer' => 'Yes!']],
                ['fields' => ['question' => 'Can I freeze it?', 'answer' => 'Absolutely.']],
            ],
        ]);

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
}
