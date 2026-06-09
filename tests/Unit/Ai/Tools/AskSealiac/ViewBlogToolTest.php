<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools\AskSealiac;

use App\Ai\State\ChatContext;
use App\Ai\Tools\AskSealiac\ViewBlogTool;
use App\Models\Blogs\Blog;
use App\Models\Blogs\BlogTag;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewBlogToolTest extends TestCase
{
    protected function tearDown(): void
    {
        ChatContext::clear();

        parent::tearDown();
    }

    #[Test]
    public function itReturnsTheBlogData(): void
    {
        $blog = $this->create(Blog::class, [
            'title' => 'My Test Blog',
            'slug' => 'my-test-blog',
            'meta_description' => 'A short description',
            'description' => 'A long description',
            'body' => 'Blog content here',
        ]);

        $tool = new ViewBlogTool();
        $result = json_decode((string) $tool->handle(new Request(['blog_id' => $blog->id])), true);

        $this->assertEquals('My Test Blog', $result['title']);
        $this->assertEquals('my-test-blog', $result['slug']);
        $this->assertEquals('A short description', $result['short_description']);
        $this->assertEquals('A long description', $result['long_description']);
        $this->assertEquals('Blog content here', $result['content']);
        $this->assertArrayHasKey('link', $result);
        $this->assertArrayHasKey('created', $result);
        $this->assertArrayHasKey('updated', $result);
    }

    #[Test]
    public function itIncludesBlogTags(): void
    {
        $blog = $this->create(Blog::class);
        $tag = $this->create(BlogTag::class, ['tag' => 'Coeliac']);

        $blog->tags()->attach($tag);

        $tool = new ViewBlogTool();
        $result = json_decode((string) $tool->handle(new Request(['blog_id' => $blog->id])), true);

        $this->assertContains('Coeliac', $result['tags']);
    }

    #[Test]
    public function itThrowsModelNotFoundForInvalidId(): void
    {
        $this->expectException(ModelNotFoundException::class);

        $tool = new ViewBlogTool();
        $tool->handle(new Request(['blog_id' => 999]));
    }

    #[Test]
    public function itTracksToolUseInChatContext(): void
    {
        $blog = $this->create(Blog::class);

        $tool = new ViewBlogTool();
        $tool->handle(new Request(['blog_id' => $blog->id]));

        $toolUses = ChatContext::getToolUses();

        $this->assertCount(1, $toolUses);
        $this->assertEquals('ViewBlogTool', $toolUses->first()['tool']);
    }
}
