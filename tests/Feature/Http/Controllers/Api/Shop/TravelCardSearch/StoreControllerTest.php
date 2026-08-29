<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api\Shop\TravelCardSearch;

use App\Actions\Shop\TravelCardSearch\SuggestTravelCardSearchTermsAction;
use App\Models\Shop\TravelCardSearchTermHistory;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class StoreControllerTest extends TestCase
{
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
    public function itTrimsTheSearchTermBeforeUsingIt(): void
    {
        $this->expectAction(SuggestTravelCardSearchTermsAction::class, ['foo'], return: collect());

        $this->postJson(route('api.shop.travel-card-search.store'), ['term' => '  foo  ']);

        $this->assertDatabaseHas(TravelCardSearchTermHistory::class, ['term' => 'foo']);
    }

    #[Test]
    public function itReturnsTheSuggestionsUnderADataKey(): void
    {
        $this->expectAction(SuggestTravelCardSearchTermsAction::class, ['foo'], return: collect([
            ['id' => 1, 'term' => '<strong>Foo</strong>bar', 'value' => 'Foobar', 'type' => 'country'],
        ]));

        $this->postJson(route('api.shop.travel-card-search.store'), ['term' => 'foo'])
            ->assertJson(fn (AssertableJson $json) => $json->has('data', 1)->has(
                'data.0',
                fn (AssertableJson $json) => $json
                    ->where('id', 1)
                    ->where('term', '<strong>Foo</strong>bar')
                    ->where('value', 'Foobar')
                    ->where('type', 'country'),
            ));
    }

    #[Test]
    public function itReturnsAnEmptyDataArrayWhenNothingIsSuggested(): void
    {
        $this->expectAction(SuggestTravelCardSearchTermsAction::class, ['foo'], return: collect());

        $this->postJson(route('api.shop.travel-card-search.store'), ['term' => 'foo'])
            ->assertJson(fn (AssertableJson $json) => $json->has('data', 0));
    }
}
