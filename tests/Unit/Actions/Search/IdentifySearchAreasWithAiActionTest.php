<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Search;

use PHPUnit\Framework\Attributes\Test;
use App\Actions\Search\IdentifySearchAreasWithAiAction;
use App\DataObjects\Search\SearchAiResponse;
use App\Models\Search\Search;
use App\Models\Search\SearchAiResponse as SearchAiResponseModel;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Resources\Chat;
use OpenAI\Responses\Chat\CreateResponse;
use Tests\TestCase;

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
        $aiResponse = $this->create(SearchAiResponseModel::class, [
            'search_id' => $searchHistory->id,
        ]);

        OpenAI::fake();

        app(IdentifySearchAreasWithAiAction::class)->handle($searchHistory);

        OpenAI::assertNothingSent();
    }

    #[Test]
    public function itUsesTheCorrectAiModel(): void
    {
        OpenAI::fake([CreateResponse::fake([
            'choices' => [
                [
                    'message' => [
                        'content' => 'foo',
                    ],
                ],
            ],
        ])]);

        app(IdentifySearchAreasWithAiAction::class)->handle($this->create(Search::class));

        OpenAI::assertSent(Chat::class, function (string $method, array $parameters): bool {
            $this->assertEquals('create', $method);

            $this->assertArrayHasKey('model', $parameters);
            $this->assertEquals('gpt-3.5-turbo-1106', $parameters['model']);

            return true;
        });
    }

    #[Test]
    public function itPassesTheCorrectPrompt(): void
    {
        OpenAI::fake([CreateResponse::fake([
            'choices' => [
                [
                    'message' => [
                        'content' => 'foo',
                    ],
                ],
            ],
        ])]);

        app(IdentifySearchAreasWithAiAction::class)->handle($this->create(Search::class, ['term' => 'foo']));

        OpenAI::assertSent(Chat::class, function (string $method, array $parameters): bool {
            $this->assertEquals('create', $method);

            $this->assertArrayHasKey('messages', $parameters);
            $this->assertIsArray($parameters['messages']);
            $this->assertCount(1, $parameters['messages']);
            $this->assertArrayHasKeys(['role', 'content'], $parameters['messages'][0]);
            $this->assertEquals('system', $parameters['messages'][0]['role']);
            $this->assertEquals(view('prompts.search', ['searchTerm' => 'foo'])->render(), $parameters['messages'][0]['content']);

            return true;
        });
    }

    #[Test]
    public function itReturnsNullIfJsonValidationFails(): void
    {
        OpenAI::fake([CreateResponse::fake([
            'choices' => [
                [
                    'message' => [
                        'content' => 'not json',
                    ],
                ],
            ],
        ])]);

        $this->assertNull(app(IdentifySearchAreasWithAiAction::class)->handle($this->create(Search::class)));
    }

    #[Test]
    public function itJsonDecodesTheResponseAndReturnsItAsASearchAiResponse(): void
    {
        OpenAI::fake([CreateResponse::fake([
            'choices' => [
                [
                    'message' => [
                        'content' => json_encode([
                            'blogs' => 10,
                            'recipes' => 20,
                            'shop' => 30,
                            'eating-out' => 40,
                            'explanation' => 'foobar',
                        ]),
                    ],
                ],
            ],
        ])]);

        $response = app(IdentifySearchAreasWithAiAction::class)->handle($this->create(Search::class));

        $this->assertInstanceOf(SearchAiResponse::class, $response);
        $this->assertEquals(10, $response->blogs);
        $this->assertEquals(20, $response->recipes);
        $this->assertEquals(30, $response->shop);
        $this->assertEquals(40, $response->eatingOut);
        $this->assertEquals('foobar', $response->reasoning);
    }

    #[Test]
    public function itCreatesAnSearchAiResponseAgainstTheHistoryClass(): void
    {
        OpenAI::fake([CreateResponse::fake([
            'choices' => [
                [
                    'message' => [
                        'content' => json_encode([
                            'blogs' => 10,
                            'recipes' => 20,
                            'shop' => 30,
                            'eating-out' => 40,
                            'explanation' => 'foobar',
                        ]),
                    ],
                ],
            ],
        ])]);

        $searchHistory = $this->create(Search::class);

        $this->assertEmpty($searchHistory->aiResponse);

        app(IdentifySearchAreasWithAiAction::class)->handle($searchHistory);

        $this->assertNotEmpty($searchHistory->refresh()->aiResponse);
    }
}
