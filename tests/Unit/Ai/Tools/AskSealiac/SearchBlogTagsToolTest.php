<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools\AskSealiac;

use App\Ai\State\ChatContext;
use App\Ai\Tools\AskSealiac\SearchBlogTagsTool;
use App\Models\Blogs\BlogTag;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SearchBlogTagsToolTest extends TestCase
{
    protected function tearDown(): void
    {
        ChatContext::clear();

        parent::tearDown();
    }

    #[Test]
    public function itReturnsMatchingBlogTags(): void
    {
        $tag = $this->create(BlogTag::class, ['tag' => 'Gluten Free Recipes', 'slug' => 'gluten-free-recipes']);
        $this->create(BlogTag::class, ['tag' => 'Travel Tips', 'slug' => 'travel-tips']);

        $tool = new SearchBlogTagsTool();
        $result = json_decode((string) $tool->handle(new Request(['term' => 'Gluten'])), true);

        $this->assertCount(1, $result);
        $this->assertEquals($tag->id, $result[0]['id']);
        $this->assertEquals('Gluten Free Recipes', $result[0]['tag']);
        $this->assertEquals('gluten-free-recipes', $result[0]['slug']);
        $this->assertArrayHasKey('link', $result[0]);
    }

    #[Test]
    public function itReturnsEmptyArrayWhenNoTagsMatch(): void
    {
        $this->create(BlogTag::class, ['tag' => 'Travel Tips']);

        $tool = new SearchBlogTagsTool();
        $result = json_decode((string) $tool->handle(new Request(['term' => 'recipes'])), true);

        $this->assertEmpty($result);
    }

    #[Test]
    public function itReturnsMultipleMatchingTags(): void
    {
        $this->create(BlogTag::class, ['tag' => 'Gluten Free Recipes']);
        $this->create(BlogTag::class, ['tag' => 'Gluten Free Travel']);
        $this->create(BlogTag::class, ['tag' => 'Dairy Free']);

        $tool = new SearchBlogTagsTool();
        $result = json_decode((string) $tool->handle(new Request(['term' => 'Gluten'])), true);

        $this->assertCount(2, $result);
    }

    #[Test]
    public function itTracksToolUseInChatContext(): void
    {
        $tool = new SearchBlogTagsTool();

        $tool->handle(new Request(['term' => 'test']));

        $toolUses = ChatContext::getToolUses();

        $this->assertCount(1, $toolUses);
        $this->assertEquals('SearchBlogTagsTool', $toolUses->first()['tool']);
        $this->assertEquals(['term' => 'test'], $toolUses->first()['data']);
    }
}
