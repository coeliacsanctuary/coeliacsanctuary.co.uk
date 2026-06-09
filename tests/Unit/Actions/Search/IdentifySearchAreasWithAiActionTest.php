<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Search;

use App\Actions\Search\IdentifySearchAreasWithAiAction;
use App\Ai\Agents\SearchAreasAgent;
use App\DataObjects\Search\SearchAiResponse;
use App\Models\Search\Search;
use App\Models\Search\SearchAiResponse as SearchAiResponseModel;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use RuntimeException;

class IdentifySearchAreasWithAiActionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    #[Test]
    public function itUsesACachedResponseFromTheDatabaseIfThereIsOneForTheSearchHistory(): void
    {
        $searchHistory = $this->create(Search::class);
        $this->create(SearchAiResponseModel::class, [
            'search_id' => $searchHistory->id,
        ]);

        SearchAreasAgent::fake();

        app(IdentifySearchAreasWithAiAction::class)->handle($searchHistory);

        SearchAreasAgent::assertNeverPrompted();
    }

    #[Test]
    public function itPromptsTheAgentWithTheSearchTerm(): void
    {
        SearchAreasAgent::fake([['shop' => 30, 'eating-out' => 40, 'blogs' => 10, 'recipes' => 20, 'explanation' => 'foobar', 'location' => null]]);

        app(IdentifySearchAreasWithAiAction::class)->handle($this->create(Search::class, ['term' => 'manchester restaurant']));

        SearchAreasAgent::assertPrompted('manchester restaurant');
    }

    #[Test]
    public function itReturnsNullIfTheAgentThrowsAnException(): void
    {
        SearchAreasAgent::fake(fn () => throw new RuntimeException('Agent failed'));

        $this->assertNull(app(IdentifySearchAreasWithAiAction::class)->handle($this->create(Search::class)));
    }

    #[Test]
    public function itReturnsASearchAiResponseFromTheAgentResult(): void
    {
        SearchAreasAgent::fake([['shop' => 30, 'eating-out' => 40, 'blogs' => 10, 'recipes' => 20, 'explanation' => 'foobar', 'location' => null]]);

        $response = app(IdentifySearchAreasWithAiAction::class)->handle($this->create(Search::class));

        $this->assertInstanceOf(SearchAiResponse::class, $response);
        $this->assertEquals(30, $response->shop);
        $this->assertEquals(40, $response->eatingOut);
        $this->assertEquals(10, $response->blogs);
        $this->assertEquals(20, $response->recipes);
        $this->assertEquals('foobar', $response->reasoning);
        $this->assertNull($response->location);
    }

    #[Test]
    public function itCreatesASearchAiResponseAgainstTheSearchRecord(): void
    {
        SearchAreasAgent::fake([['shop' => 30, 'eating-out' => 40, 'blogs' => 10, 'recipes' => 20, 'explanation' => 'foobar', 'location' => null]]);

        $searchHistory = $this->create(Search::class);

        $this->assertEmpty($searchHistory->aiResponse);

        app(IdentifySearchAreasWithAiAction::class)->handle($searchHistory);

        $this->assertNotEmpty($searchHistory->refresh()->aiResponse);
    }
}
