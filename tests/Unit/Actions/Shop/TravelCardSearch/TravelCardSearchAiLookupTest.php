<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop\TravelCardSearch;

use App\Ai\Agents\TravelCardSearchAgent;
use App\Actions\Shop\TravelCardSearch\SearchTravelCardCountyOrLanguageAction;
use App\Actions\Shop\TravelCardSearch\TravelCardSearchAiLookupAction;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TravelCardSearchAiLookupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        TravelCardSearchAgent::fake([['results' => ['foobar'], 'explanation' => 'test']]);
    }

    #[Test]
    public function itPromptsTheTravelCardSearchAgentWithTheSearchTerm(): void
    {
        app(TravelCardSearchAiLookupAction::class)->handle('foo');

        TravelCardSearchAgent::assertPrompted('foo');
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
        TravelCardSearchAgent::fake([['results' => [], 'explanation' => 'no match']]);

        $this->dontExpectAction(SearchTravelCardCountyOrLanguageAction::class);

        app(TravelCardSearchAiLookupAction::class)->handle('foo');
    }
}
