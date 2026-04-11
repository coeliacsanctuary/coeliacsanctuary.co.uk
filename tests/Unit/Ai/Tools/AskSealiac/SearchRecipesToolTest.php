<?php

declare(strict_types=1);

namespace Tests\Unit\Ai\Tools\AskSealiac;

use App\Ai\State\ChatContext;
use App\Ai\Tools\AskSealiac\SearchRecipesTool;
use Laravel\Ai\Tools\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SearchRecipesToolTest extends TestCase
{
    protected function tearDown(): void
    {
        ChatContext::clear();

        parent::tearDown();
    }

    #[Test]
    public function itReturnsEmptyWhenNoRecipesMatch(): void
    {
        $tool = new SearchRecipesTool();
        $result = json_decode((string) $tool->handle(new Request(['value' => 'xyznonexistent'])), true);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    #[Test]
    public function itTracksToolUseInChatContext(): void
    {
        $tool = new SearchRecipesTool();
        $tool->handle(new Request(['value' => 'cake']));

        $toolUses = ChatContext::getToolUses();

        $this->assertCount(1, $toolUses);
        $this->assertEquals('SearchRecipesTool', $toolUses->first()['tool']);
    }

    #[Test]
    public function itHasTheCorrectSchema(): void
    {
        $tool = new SearchRecipesTool();
        $schema = new \Illuminate\JsonSchema\JsonSchemaTypeFactory();

        $result = $tool->schema($schema);

        $this->assertArrayHasKey('value', $result);
        $this->assertArrayHasKey('allergens', $result);
        $this->assertArrayHasKey('meals', $result);
        $this->assertArrayHasKey('features', $result);
    }

    #[Test]
    public function itHasADescription(): void
    {
        $tool = new SearchRecipesTool();

        $this->assertNotEmpty((string) $tool->description());
    }
}
