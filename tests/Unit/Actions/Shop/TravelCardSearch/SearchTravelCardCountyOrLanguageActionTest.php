<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop\TravelCardSearch;

use App\Actions\Shop\TravelCardSearch\SearchTravelCardCountyOrLanguageAction;
use App\Models\Shop\TravelCardSearchTerm;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SearchTravelCardCountyOrLanguageActionTest extends TestCase
{
    /** @return Collection<int, array{id: int, term: string, value: string, type: string}> */
    protected function search(string $searchString): Collection
    {
        return $this->callAction(SearchTravelCardCountyOrLanguageAction::class, $searchString);
    }

    #[Test]
    public function itReturnsMatchingResults(): void
    {
        $check = $this->create(TravelCardSearchTerm::class, [
            'term' => 'foobar',
        ]);

        foreach (['foo', 'bar', 'foobar', 'fOoBaR'] as $searchString) {
            $result = $this->search($searchString);

            $this->assertCount(1, $result);
            $this->assertEquals($check->id, $result[0]['id']);
        }
    }

    #[Test]
    public function itReturnsAFormattedResponse(): void
    {
        $this->create(TravelCardSearchTerm::class, ['term' => 'foobar']);

        $result = $this->search('foo');

        $this->assertArrayHasKeys(['id', 'term', 'value', 'type'], $result[0]);
        $this->assertEquals('<strong>Foo</strong>bar', $result[0]['term']);
        $this->assertEquals('Foobar', $result[0]['value']);

        $this->assertEquals('Foo<strong>bar</strong>', $this->search('bar')[0]['term']);
        $this->assertEquals('<strong>Foobar</strong>', $this->search('foobar')[0]['term']);
    }

    #[Test]
    public function itHighlightsRegardlessOfTheCaseTyped(): void
    {
        $this->create(TravelCardSearchTerm::class, ['term' => 'spain']);

        $this->assertEquals('<strong>Spa</strong>in', $this->search('Spa')[0]['term']);
        $this->assertEquals('<strong>Spa</strong>in', $this->search('spa')[0]['term']);
        $this->assertEquals('<strong>Spa</strong>in', $this->search('SPA')[0]['term']);
    }

    #[Test]
    public function itPutsAnExactMatchFirst(): void
    {
        $this->create(TravelCardSearchTerm::class, ['term' => 'Equatorial Guinea']);
        $this->create(TravelCardSearchTerm::class, ['term' => 'Guinea']);
        $this->create(TravelCardSearchTerm::class, ['term' => 'Papua New Guinea']);

        $this->assertEquals('Guinea', $this->search('Guinea')[0]['value']);
    }

    #[Test]
    public function itOrdersOnMatchPositionThenLengthWhenThereIsNoExactMatch(): void
    {
        $this->create(TravelCardSearchTerm::class, ['term' => 'South Sudanese']);
        $this->create(TravelCardSearchTerm::class, ['term' => 'Sudanese']);
        $this->create(TravelCardSearchTerm::class, ['term' => 'Sudanese Arabic']);

        $this->assertEquals(
            ['Sudanese', 'Sudanese Arabic', 'South Sudanese'],
            $this->search('Sudanese')->pluck('value')->all(),
        );
    }

    #[Test]
    public function itLeavesTermsThatAlreadyCarryACapitalAlone(): void
    {
        $this->create(TravelCardSearchTerm::class, ['term' => 'UAE']);

        $this->assertEquals('UAE', $this->search('UAE')[0]['value']);
    }

    #[Test]
    public function itTreatsLikeWildcardsAsLiteralCharacters(): void
    {
        $this->create(TravelCardSearchTerm::class, ['term' => 'spain']);

        $this->assertCount(0, $this->search('%'));
        $this->assertCount(0, $this->search('_pain'));
    }

    #[Test]
    public function itReturnsNothingForAnEmptySearch(): void
    {
        $this->create(TravelCardSearchTerm::class, ['term' => 'spain']);

        $this->assertCount(0, $this->search(''));
        $this->assertCount(0, $this->search('   '));
    }
}
