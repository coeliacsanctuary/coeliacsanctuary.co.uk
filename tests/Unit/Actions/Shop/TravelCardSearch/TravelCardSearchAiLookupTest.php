<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop\TravelCardSearch;

use App\Actions\Shop\TravelCardSearch\TravelCardSearchAiLookupAction;
use App\Ai\Agents\TravelCardSearchAgent;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TravelCardSearchAiLookupTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        TravelCardSearchAgent::fake([['results' => ['Spain'], 'explanation' => 'test']]);
    }

    /** @return Collection<int, string> */
    protected function lookup(string $searchTerm = 'benidorm'): Collection
    {
        return $this->callAction(TravelCardSearchAiLookupAction::class, $searchTerm);
    }

    #[Test]
    public function itPromptsTheTravelCardSearchAgentWithTheSearchTerm(): void
    {
        $this->lookup('benidorm');

        TravelCardSearchAgent::assertPrompted('benidorm');
    }

    #[Test]
    public function itReturnsTheCountriesTheAgentResolved(): void
    {
        $this->assertEquals(['Spain'], $this->lookup()->all());
    }

    #[Test]
    public function itReturnsEveryCountryForAMultiPlaceSearch(): void
    {
        TravelCardSearchAgent::fake([['results' => ['Spain', 'Greece'], 'explanation' => 'two places']]);

        $this->assertEquals(['Spain', 'Greece'], $this->lookup('benidorm and rhodes')->all());
    }

    #[Test]
    public function itReturnsNothingWhenTheAgentFindsNoMatch(): void
    {
        TravelCardSearchAgent::fake([['results' => [], 'explanation' => 'no match']]);

        $this->assertCount(0, $this->lookup('asdfgh'));
    }

    #[Test]
    public function itDiscardsBlankResults(): void
    {
        TravelCardSearchAgent::fake([['results' => ['Spain', '  ', ''], 'explanation' => 'padded']]);

        $this->assertEquals(['Spain'], $this->lookup()->all());
    }
}
