<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Blogs;

use App\Actions\Blogs\FindRelatedBlogsAction;
use PHPUnit\Framework\Attributes\Test;
use App\Actions\Comments\GetCommentsForItemAction;
use App\Models\Blogs\Blog;
use App\Models\Faqs\Faq;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ShowControllerTest extends TestCase
{
    protected Blog $blog;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withBlogs(1);

        $this->blog = Blog::query()->first();
    }

    #[Test]
    public function itReturnsNotFoundForABlogThatDoesntExist(): void
    {
        $this->get(route('blog.show', ['blog' => 'foobar']))->assertNotFound();
    }

    protected function visitBlog(): TestResponse
    {
        return $this->get(route('blog.show', ['blog' => $this->blog]));
    }

    #[Test]
    public function itReturnsNotFoundForABlogThatIsntLive(): void
    {
        $this->blog->update(['live' => false]);

        $this->visitBlog()->assertNotFound();
    }

    #[Test]
    public function itReturnsOkForABlogThatIsLive(): void
    {
        $this->visitBlog()->assertOk();
    }

    #[Test]
    public function itCallsTheGetCommentsForItemAction(): void
    {
        $this->expectAction(GetCommentsForItemAction::class, [Blog::class]);

        $this->visitBlog();
    }

    #[Test]
    public function itCallsTheFindRelatedBlogsAction(): void
    {
        $this->expectAction(FindRelatedBlogsAction::class, [Blog::class]);

        $this->visitBlog();
    }

    #[Test]
    public function itRendersTheInertiaPage(): void
    {
        $this->visitBlog()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Blog/Show')
                    ->has('blog')
                    ->where('blog.title', 'Blog 0')
                    ->etc()
            );
    }

    /** @return string[] */
    protected function schemaFor(TestResponse $response): array
    {
        /** @var string[] $schema */
        $schema = $response->viewData('page')['props']['meta']['schema'];

        return $schema;
    }

    #[Test]
    public function itSchemasTheBlogAsABlogPosting(): void
    {
        $schema = $this->schemaFor($this->visitBlog());

        $this->assertStringContainsString('"@type":"BlogPosting"', implode('', $schema));
    }

    #[Test]
    public function itPointsTheMainEntityOfPageAtTheBlogRatherThanTheHomepage(): void
    {
        $schema = $this->schemaFor($this->visitBlog());

        $this->assertStringContainsString(
            '"mainEntityOfPage":{"@type":"WebPage","@id":"' . $this->blog->absolute_link . '"}',
            implode('', $schema)
        );
    }

    #[Test]
    public function itDoesntSchemaTheFaqsWhenTheBlogHasNone(): void
    {
        $schema = $this->schemaFor($this->visitBlog());

        $this->assertStringNotContainsString('FAQPage', implode('', $schema));
    }

    #[Test]
    public function itSchemasTheFaqsWhenTheBlogHasSome(): void
    {
        $this->build(Faq::class)->on($this->blog)->create(['question' => 'Is this gluten free?', 'answer' => 'Yes!']);

        $schema = implode('', $this->schemaFor($this->visitBlog()));

        $this->assertStringContainsString('"@type":"FAQPage"', $schema);
        $this->assertStringContainsString('"name":"Is this gluten free?"', $schema);
        $this->assertStringContainsString('"text":"Yes!"', $schema);
    }

    #[Test]
    public function itUsesTheOpenGraphArticleTagProperty(): void
    {
        $metas = $this->visitBlog()->viewData('page')['props']['meta']['alternateMetas'];

        $this->assertArrayHasKey('article:tag', $metas);
        $this->assertArrayNotHasKey('article.tags', $metas);
    }

    #[Test]
    public function itDoesntDuplicateTheArticlePublisherAlreadyInTheLayout(): void
    {
        $metas = $this->visitBlog()->viewData('page')['props']['meta']['alternateMetas'];

        $this->assertArrayNotHasKey('article:publisher', $metas);
    }
}
