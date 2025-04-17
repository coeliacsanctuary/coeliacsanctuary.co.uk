<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Shop\TravelCardSearch;

use App\Actions\Shop\TravelCardSearch\SearchTravelCardCountyOrLanguageAction;
use App\Actions\Shop\TravelCardSearch\TravelCardSearchAiLookup;
use App\Models\Shop\TravelCardSearchTerm;
use App\Models\Shop\TravelCardSearchTermHistory;
use Illuminate\Testing\Fluent\AssertableJson;
use OpenAI\Laravel\Facades\OpenAI;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        OpenAI::fake();
    }

    #[Test]
    public function itErrorsWithoutASearchTerm(): void
    {
        $this->postJson(route('api.shop.travel-card-search.store'))
            ->assertJsonValidationErrorFor('term');
    }

    #[Test]
    public function itCreatesASearchHistoryRecordForTheSearchTerm(): void
    {
        $this->assertDatabaseEmpty(TravelCardSearchTermHistory::class);

        $this->postJson(route('api.shop.travel-card-search.store'), ['term' => 'foo']);

        $this->assertDatabaseHas(TravelCardSearchTermHistory::class, [
            'term' => 'foo',
            'hits' => 1,
        ]);
    }

    #[Test]
    public function itUpdatesTheHitsOnAnExistingSearchTermIfOneExists(): void
    {
        $searchTerm = $this->create(TravelCardSearchTermHistory::class, [
            'term' => 'foo',
            'hits' => 5,
        ]);

        $this->postJson(route('api.shop.travel-card-search.store'), ['term' => 'foo']);

        $this->assertEquals(6, $searchTerm->refresh()->hits);
    }

    #[Test]
    public function itCallsTheSearchTravelCardCountryOrLanguageAction(): void
    {
        $this->expectAction(SearchTravelCardCountyOrLanguageAction::class, ['foo'], return: collect(['foo' => 'bar']));
        $this->dontExpectAction(TravelCardSearchAiLookup::class);

        $this->postJson(route('api.shop.travel-card-search.store'), ['term' => 'foo']);
    }

    #[Test]
    public function itCallsTheTravelCardSearchAiLookupActionIfThereAreNoResults(): void
    {
        $this->expectAction(SearchTravelCardCountyOrLanguageAction::class, ['foo'], return: collect([]));
        $this->expectAction(TravelCardSearchAiLookup::class, ['foo'], return: collect(['foo ' => 'bar']));

        $this->postJson(route('api.shop.travel-card-search.store'), ['term' => 'foo']);
    }

    #[Test]
    public function itReturnsMatchSearchTerms(): void
    {
        $this->build(TravelCardSearchTerm::class)
            ->count(2)
            ->sequence(
                ['term' => 'foobar', 'type' => 'country'],
                ['term' => 'barfoo', 'type' => 'language'],
                ['term' => 'baz'],
            )
            ->create();

        $this->postJson(route('api.shop.travel-card-search.store'), ['term' => 'foo'])
            ->assertJson(
                fn (AssertableJson $json) => $json
                    ->has('data', 2)
                    ->has(
                        'data.0',
                        fn (AssertableJson $json) => $json
                            ->where('type', 'country')
                            ->where('term', '<strong>foo</strong>bar')
                            ->etc()
                    )
                    ->has(
                        'data.1',
                        fn (AssertableJson $json) => $json
                            ->where('type', 'language')
                            ->where('term', 'bar<strong>foo</strong>')
                            ->etc()
                    )
            );
    }
}
