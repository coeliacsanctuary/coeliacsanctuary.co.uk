<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\Shop\TravelCardSearch;

use App\Actions\Shop\TravelCardSearch\MatchTravelCardSearchTermsAction;
use App\Models\Shop\TravelCardSearchTerm;
use Illuminate\Database\Eloquent\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MatchTravelCardSearchTermsActionTest extends TestCase
{
    protected function term(string $term): TravelCardSearchTerm
    {
        /** @var TravelCardSearchTerm $searchTerm */
        $searchTerm = $this->create(TravelCardSearchTerm::class, ['term' => $term, 'type' => 'country']);

        return $searchTerm;
    }

    /** @return Collection<int, TravelCardSearchTerm> */
    protected function match(string $searchString): Collection
    {
        return $this->callAction(MatchTravelCardSearchTermsAction::class, $searchString);
    }

    #[Test]
    public function itMatchesASingleTerm(): void
    {
        $this->term('Spain');

        $this->assertEquals(['Spain'], $this->match('Spain')->pluck('term')->all());
    }

    #[Test]
    public function itMatchesOnAPartialTerm(): void
    {
        $this->term('Spain');

        $this->assertEquals(['Spain'], $this->match('spa')->pluck('term')->all());
    }

    #[Test]
    public function itPrefersAnExactMatchOverALongerContainingTerm(): void
    {
        $this->term('Equatorial Guinea');
        $this->term('Guinea');

        $this->assertEquals(['Guinea'], $this->match('Guinea')->pluck('term')->all());
    }

    #[Test]
    public function itReturnsNothingWhenNothingMatches(): void
    {
        $this->term('Spain');

        $this->assertCount(0, $this->match('asdfgh'));
    }

    #[Test]
    public function itReturnsNothingForAnEmptySearch(): void
    {
        $this->term('Spain');

        $this->assertCount(0, $this->match(''));
        $this->assertCount(0, $this->match('   '));
    }

    #[Test]
    public function itMatchesTheWholeStringBeforeSplittingIt(): void
    {
        $this->term('Trinidad and Tobago');
        $this->term('Trinidad');
        $this->term('Tobago');

        $this->assertEquals(['Trinidad and Tobago'], $this->match('Trinidad and Tobago')->pluck('term')->all());
    }

    #[Test]
    #[DataProvider('separatorProvider')]
    public function itSplitsOnEachSupportedSeparator(string $searchString): void
    {
        $this->term('Spain');
        $this->term('France');

        $this->assertEquals(['Spain', 'France'], $this->match($searchString)->pluck('term')->all());
    }

    /** @return array<string, array<int, string>> */
    public static function separatorProvider(): array
    {
        return [
            'and' => ['Spain and France'],
            'AND uppercased' => ['Spain AND France'],
            'ampersand' => ['Spain & France'],
            'plus' => ['Spain + France'],
            'slash' => ['Spain/France'],
            'slash with spaces' => ['Spain / France'],
            'comma' => ['Spain, France'],
        ];
    }

    #[Test]
    public function itSplitsMoreThanTwoDestinations(): void
    {
        $this->term('Spain');
        $this->term('France');
        $this->term('Italy');

        $this->assertEquals(
            ['Spain', 'France', 'Italy'],
            $this->match('Spain, France and Italy')->pluck('term')->all(),
        );
    }

    #[Test]
    public function itReturnsNothingWhenOnlyPartOfTheSearchMatches(): void
    {
        $this->term('Spain');

        $this->assertCount(0, $this->match('Spain and Narnia'));
    }

    #[Test]
    public function itDeduplicatesRepeatedDestinations(): void
    {
        $this->term('Spain');

        $this->assertEquals(['Spain'], $this->match('Spain and spain')->pluck('term')->all());
    }

    #[Test]
    public function itIgnoresLikeWildcards(): void
    {
        $this->term('Spain');

        $this->assertCount(0, $this->match('%'));
        $this->assertCount(0, $this->match('_pain'));
    }
}
