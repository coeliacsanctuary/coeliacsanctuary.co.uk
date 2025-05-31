<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Search;

use PHPUnit\Framework\Attributes\Test;
use App\Actions\Search\IdentifySearchAreasWithAiAction;
use App\DataObjects\Search\SearchAiResponse;
use App\DataObjects\Search\SearchParameters;
use App\Models\Search\Search;
use App\Models\SearchHistory;
use App\Pipelines\Search\PerformSearchPipeline;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    #[Test]
    public function itCreatesASearchRecordIfOneDoesntExist(): void
    {
        $this->assertDatabaseEmpty(Search::class);

        $this->get(route('search.index', ['q' => 'foo']));

        $this->assertDatabaseCount(Search::class, 1);
        $this->assertEquals('foo', Search::query()->first()->term);
    }

    #[Test]
    public function itCreatesASearchHistoryRecordForTheSearch(): void
    {
        $this->assertDatabaseEmpty(SearchHistory::class);
        $this->assertDatabaseEmpty(Search::class);

        $this->get(route('search.index', ['q' => 'foo']));

        $this->assertDatabaseCount(SearchHistory::class, 1);
        $this->assertDatabaseCount(Search::class, 1);
    }

    #[Test]
    public function itDoesntCallTheAiSearchActionIfTheRequestWasMadeFromTheSearchPage(): void
    {
        $this->mock(IdentifySearchAreasWithAiAction::class)->shouldNotReceive('handle');

        $this->from(route('search.index', ['q' => 'foo']))
            ->get(route('search.index', ['q' => 'bar']));
    }

    #[Test]
    public function itCallsTheAiSearchActionIfSearchingFromElsewhere(): void
    {
        $this->mock(IdentifySearchAreasWithAiAction::class)
            ->shouldReceive('handle')
            ->andReturnNull()
            ->once();

        $this->from(route('home'))
            ->get(route('search.index', ['q' => 'foo']));
    }

    #[Test]
    public function itSetsTheSearchParametersUsingTheScoreFromTheAiResultIfItIsGreaterThan10(): void
    {
        $this->mock(IdentifySearchAreasWithAiAction::class)
            ->shouldReceive('handle')
            ->andReturn(new SearchAiResponse(
                shop: 5,
                eatingOut: 20,
                blogs: 50,
                recipes: 10,
                reasoning: 'foo',
            ))
            ->once();

        $this->mock(PerformSearchPipeline::class)
            ->shouldReceive('run')
            ->withArgs(function (SearchParameters $searchParameters) {
                $this->assertFalse($searchParameters->shop);
                $this->assertTrue($searchParameters->eateries);
                $this->assertTrue($searchParameters->blogs);
                $this->assertTrue($searchParameters->recipes);

                return true;
            })
            ->once();

        $this->from(route('home'))
            ->get(route('search.index', [
                'q' => 'foo',
                'shop' => 'true',
                'eateries' => 'false',
                'blogs' => 'false',
                'recipes' => false,
            ]));
    }

    #[Test]
    public function itSetsTheLocationOnTheSearchParamsIfOneIsPresentFromTheAi(): void
    {
        $this->mock(IdentifySearchAreasWithAiAction::class)
            ->shouldReceive('handle')
            ->andReturn(new SearchAiResponse(
                shop: 5,
                eatingOut: 20,
                blogs: 50,
                recipes: 10,
                reasoning: 'foo',
                location: 'bar',
            ))
            ->once();

        $this->mock(PerformSearchPipeline::class)
            ->shouldReceive('run')
            ->withArgs(function (SearchParameters $searchParameters) {
                $this->assertEquals('bar', $searchParameters->locationSearch);

                return true;
            })
            ->once();

        $this->from(route('home'))
            ->get(route('search.index', ['q' => 'foo']));
    }

    #[Test]
    public function itCallsThePerformSearchPipeline(): void
    {
        $this->expectPipelineToRun(PerformSearchPipeline::class);

        $this->get(route('search.index', ['q' => 'foo']));
    }

    #[Test]
    public function itReturnsTheInertiaView(): void
    {
        $this
            ->from(route('search.index'))
            ->get(route('search.index', [
                'q' => 'foo',
                'blogs' => 'true',
                'recipes' => 'false',
                'eateries' => 'true',
                'shop' => 'false',
            ]))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('Search/Index')
                    ->has(
                        'parameters',
                        fn (Assert $page) => $page
                            ->where('q', 'foo')
                            ->where('blogs', true)
                            ->where('recipes', false)
                            ->where('eateries', true)
                            ->where('shop', false),
                    )
                    ->has('location')
                    ->has('searchedLatLng')
                    ->has('hasEatery')
                    ->has('aiAssisted')
            );
    }
}
