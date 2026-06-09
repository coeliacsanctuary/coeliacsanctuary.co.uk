<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools\AskSealiac;

use App\Ai\State\ChatContext;
use App\Ai\Tools\AskSealiac\ViewBlogsForBlogTagTool;
use App\Models\Blogs\Blog;
use App\Models\Blogs\BlogTag;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewBlogsForBlogTagToolTest extends TestCase
{
    protected function tearDown(): void
    {
        ChatContext::clear();

        parent::tearDown();
    }

    #[Test]
    public function itReturnsBlogsForAGivenTag(): void
    {
        $tag = $this->create(BlogTag::class, ['tag' => 'Coeliac']);
        $blog = $this->create(Blog::class, ['title' => 'Tagged Blog']);
        $blog->tags()->attach($tag);

        $untaggedBlog = $this->create(Blog::class, ['title' => 'Untagged Blog']);

        $tool = new ViewBlogsForBlogTagTool();
        $result = json_decode((string) $tool->handle(new Request(['tag_id' => [$tag->id]])), true);

        $this->assertCount(1, $result);
        $this->assertEquals('Tagged Blog', $result[0]['title']);
        $this->assertArrayHasKey('link', $result[0]);
        $this->assertArrayHasKey('short_description', $result[0]);
        $this->assertArrayHasKey('long_description', $result[0]);
        $this->assertContains('Coeliac', $result[0]['tags']);
    }

    #[Test]
    public function itReturnsBlogsForMultipleTags(): void
    {
        $tagA = $this->create(BlogTag::class);
        $tagB = $this->create(BlogTag::class);

        $blogA = $this->create(Blog::class, ['title' => 'Blog A']);
        $blogA->tags()->attach($tagA);

        $blogB = $this->create(Blog::class, ['title' => 'Blog B']);
        $blogB->tags()->attach($tagB);

        $tool = new ViewBlogsForBlogTagTool();
        $result = json_decode((string) $tool->handle(new Request(['tag_id' => [$tagA->id, $tagB->id]])), true);

        $this->assertCount(2, $result);
    }

    #[Test]
    public function itReturnsBlogsOrderedByNewestFirst(): void
    {
        $tag = $this->create(BlogTag::class);

        $olderBlog = $this->create(Blog::class, [
            'title' => 'Older Blog',
            'created_at' => now()->subDays(5),
        ]);
        $olderBlog->tags()->attach($tag);

        $newerBlog = $this->create(Blog::class, [
            'title' => 'Newer Blog',
            'created_at' => now(),
        ]);
        $newerBlog->tags()->attach($tag);

        $tool = new ViewBlogsForBlogTagTool();
        $result = json_decode((string) $tool->handle(new Request(['tag_id' => [$tag->id]])), true);

        $this->assertEquals('Newer Blog', $result[0]['title']);
        $this->assertEquals('Older Blog', $result[1]['title']);
    }

    #[Test]
    public function itReturnsEmptyArrayWhenNoMatchingBlogs(): void
    {
        $tag = $this->create(BlogTag::class);

        $tool = new ViewBlogsForBlogTagTool();
        $result = json_decode((string) $tool->handle(new Request(['tag_id' => [$tag->id]])), true);

        $this->assertEmpty($result);
    }

    #[Test]
    public function itTracksToolUseInChatContext(): void
    {
        $tag = $this->create(BlogTag::class);

        $tool = new ViewBlogsForBlogTagTool();
        $tool->handle(new Request(['tag_id' => [$tag->id]]));

        $toolUses = ChatContext::getToolUses();

        $this->assertCount(1, $toolUses);
        $this->assertEquals('ViewBlogsForBlogTagTool', $toolUses->first()['tool']);
    }
}
