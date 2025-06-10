<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop\TravelCardSearch;

use App\Actions\Shop\TravelCardSearch\SearchTravelCardCountyOrLanguageAction;
use App\Actions\Shop\TravelCardSearch\TravelCardSearchAiLookupAction;
use App\Support\Ai\Prompts\TravelCardLookupPrompt;
use OpenAI\Laravel\Facades\OpenAI;
use OpenAI\Resources\Chat;
use OpenAI\Responses\Chat\CreateResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TravelCardSearchAiLookupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        OpenAI::fake([
            CreateResponse::fake([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'results' => ['foobar'],
                        ]),
                    ],
                ]],
            ]),
        ]);
    }

    #[Test]
    public function itUsesTheTravelCardLookupPrompt(): void
    {
        app(TravelCardSearchAiLookupAction::class)->handle('foo');

        OpenAI::assertSent(Chat::class, function (string $method, array $data) {
            $this->assertEquals('create', $method);
            $message = $data['messages'][0];

            $this->assertArrayHasKey('role', $message);
            $this->assertEquals('system', $message['role']);

            $this->assertArrayHasKey('content', $message);
            $this->assertEquals(TravelCardLookupPrompt::get('foo'), $message['content']);

            return true;
        });
    }

    #[Test]
    public function itCallsTheSearchTravelCardCountyOrLanguageActionWithTheResult(): void
    {
        $this->expectAction(SearchTravelCardCountyOrLanguageAction::class, ['foobar'], return: collect());

        app(TravelCardSearchAiLookupAction::class)->handle('foo');
    }

    #[Test]
    public function itDoesntCallTheActionIfThereIsNoResult(): void
    {
        OpenAI::fake([
            CreateResponse::fake([
                'choices' => [[
                    'message' => [
                        'content' => json_encode([
                            'results' => [],
                        ]),
                    ],
                ]],
            ]),
        ]);

        $this->dontExpectAction(SearchTravelCardCountyOrLanguageAction::class);

        app(TravelCardSearchAiLookupAction::class)->handle('foo');
    }

    #[Test]
    public function itHandlesTheExceptionIfTheResultIsntValidJson(): void
    {
        OpenAI::fake([
            CreateResponse::fake([
                'choices' => [[
                    'message' => [
                        'content' => 'foo',
                    ],
                ]],
            ]),
        ]);

        $this->dontExpectAction(SearchTravelCardCountyOrLanguageAction::class);

        app(TravelCardSearchAiLookupAction::class)->handle('foo');
    }
}
