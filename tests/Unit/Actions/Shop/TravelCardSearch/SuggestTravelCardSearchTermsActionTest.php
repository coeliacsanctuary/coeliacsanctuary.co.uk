<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop\TravelCardSearch;

use App\Actions\Shop\TravelCardSearch\SuggestTravelCardSearchTermsAction;
use App\Actions\Shop\TravelCardSearch\TravelCardSearchAiLookupAction;
use App\Models\Shop\TravelCardSearchTerm;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SuggestTravelCardSearchTermsActionTest extends TestCase
{
    protected function term(string $term, string $type = 'country'): TravelCardSearchTerm
    {
        /** @var TravelCardSearchTerm $searchTerm */
        $searchTerm = $this->create(TravelCardSearchTerm::class, ['term' => $term, 'type' => $type]);

        return $searchTerm;
    }

    /** @return Collection<int, array{id: int|null, term: string, value: string, type: string}> */
    protected function suggest(string $searchString): Collection
    {
        return $this->callAction(SuggestTravelCardSearchTermsAction::class, $searchString);
    }

    #[Test]
    public function itReturnsDirectMatchesWithoutAskingTheAgent(): void
    {
        $this->term('Spain');

        $this->dontExpectAction(TravelCardSearchAiLookupAction::class);

        $this->assertEquals(['Spain'], $this->suggest('Spain')->pluck('value')->all());
    }

    #[Test]
    public function itLeadsWithACombinedRowForAMultiDestinationSearch(): void
    {
        $this->term('Spain');
        $this->term('France');

        $this->dontExpectAction(TravelCardSearchAiLookupAction::class);

        $results = $this->suggest('Spain and France');

        $this->assertCount(3, $results);

        $this->assertEquals([
            'id' => null,
            'term' => 'Spain and France',
            'value' => 'Spain and France',
            'type' => '2 destinations',
        ], $results[0]);

        $this->assertEquals(['Spain', 'France'], $results->skip(1)->pluck('value')->all());
    }

    #[Test]
    public function itLabelsTheCombinedRowWithTheNumberOfDestinations(): void
    {
        $this->term('Spain');
        $this->term('France');
        $this->term('Italy');

        $this->assertEquals('3 destinations', $this->suggest('Spain, France and Italy')[0]['type']);
    }

    #[Test]
    public function itAsksTheAgentWhenNothingMatches(): void
    {
        $this->term('Spain');

        $this->expectAction(TravelCardSearchAiLookupAction::class, ['benidorm'], return: collect(['Spain']));

        $this->assertEquals(['Spain'], $this->suggest('benidorm')->pluck('value')->all());
    }

    #[Test]
    public function itSearchesOnEveryCountryTheAgentReturns(): void
    {
        $this->term('Spain');
        $this->term('Greece');

        $this->expectAction(
            TravelCardSearchAiLookupAction::class,
            ['benidorm and rhodes'],
            return: collect(['Spain', 'Greece']),
        );

        $results = $this->suggest('benidorm and rhodes');

        $this->assertEquals('2 destinations', $results[0]['type']);
        $this->assertEquals(['Spain', 'Greece'], $results->skip(1)->pluck('value')->all());
    }

    #[Test]
    public function itReturnsNothingWhenTheAgentFindsNothingEither(): void
    {
        $this->expectAction(TravelCardSearchAiLookupAction::class, ['asdfgh'], return: collect());

        $this->assertCount(0, $this->suggest('asdfgh'));
    }

    #[Test]
    public function itReturnsNothingWhenTheAgentResolvesToSomethingUnstocked(): void
    {
        $this->term('Spain');

        $this->expectAction(TravelCardSearchAiLookupAction::class, ['narnia'], return: collect(['Narnia']));

        $this->assertCount(0, $this->suggest('narnia'));
    }
}
