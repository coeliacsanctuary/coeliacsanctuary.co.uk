<?php

declare(strict_types=1);

namespace Tests\Unit\Pipelines\EatingOut\GetEateries\Steps;

use PHPUnit\Framework\Attributes\Test;
use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\DataObjects\EatingOut\LatLng;
use App\DataObjects\EatingOut\PendingEatery;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryVenueType;
use App\Services\EatingOut\LocationSearchService;
use App\Support\State\EatingOut\Search\SearchResultIdsState;
use Illuminate\Support\Collection;

class GetEateriesInSearchAreaTest extends GetEateriesTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $london = ['lat' => 51.5, 'lng' => -0.1];

        $this->mock(LocationSearchService::class)
            ->expects('getLatLng')
            ->zeroOrMoreTimes()
            ->andReturn(new LatLng($london['lat'], $london['lng']));
    }

    #[Test]
    public function itReturnsTheNextClosureInTheAction(): void
    {
        $this->assertInstanceOf(GetEateriesPipelineData::class, $this->callGetEateriesInSearchAreaAction());
    }

    #[Test]
    public function itReturnsEachEateryAPendingEatery(): void
    {
        $collection = $this->callGetEateriesInSearchAreaAction()->eateries;

        $collection->each(fn ($item) => $this->assertInstanceOf(PendingEatery::class, $item));
    }

    #[Test]
    public function itAppendsToThePassedInCollection(): void
    {
        $eateries = new Collection(range(0, 4));

        $newCollection = $this->callGetEateriesInSearchAreaAction($eateries)->eateries;

        $this->assertCount(10, $newCollection); // 5 in setup, 5 from above
    }

    #[Test]
    public function itCanFilterTheEateriesByCategory(): void
    {
        $eatery = $this->build(Eatery::class)
            ->create([
                'type_id' => 2,
                'county_id' => $this->county->id,
                'town_id' => $this->town->id,
                'venue_type_id' => EateryVenueType::query()->first()->id,
                'lat' => 51.50,
                'lng' => -0.12,
            ]);

        $eateries = $this->callGetEateriesInSearchAreaAction(filters: ['categories' => ['att']]);

        $this->assertCount(1, $eateries->eateries);
        $this->assertEquals($eatery->id, $eateries->eateries->first()->id);
    }

    #[Test]
    public function itCanFilterTheEateriesByVenueType(): void
    {
        $venueType = $this->create(EateryVenueType::class, ['slug' => 'test']);

        $eatery = $this->build(Eatery::class)
            ->create([
                'type_id' => 1,
                'county_id' => $this->county->id,
                'town_id' => $this->town->id,
                'venue_type_id' => $venueType->id,
                'lat' => 51.50,
                'lng' => -0.12,
            ]);

        $eateries = $this->callGetEateriesInSearchAreaAction(filters: ['venueTypes' => ['test']]);

        $this->assertCount(1, $eateries->eateries);
        $this->assertEquals($eatery->id, $eateries->eateries->first()->id);
    }

    #[Test]
    public function itCanFilterTheEateriesByFeature(): void
    {
        $feature = $this->create(EateryFeature::class, ['slug' => 'test']);

        $eatery = $this->build(Eatery::class)
            ->create([
                'type_id' => 1,
                'county_id' => $this->county->id,
                'town_id' => $this->town->id,
                'lat' => 51.50,
                'lng' => -0.12,
            ]);

        $feature->eateries()->attach($eatery);

        $eateries = $this->callGetEateriesInSearchAreaAction(filters: ['features' => ['test']]);

        $this->assertCount(1, $eateries->eateries);
        $this->assertEquals($eatery->id, $eateries->eateries->first()->id);
    }

    #[Test]
    public function itDoesntGetEateriesThatAreMarkedAsClosedDown(): void
    {
        Eatery::query()->update(['closed_down' => true]);

        $eateries = $this->callGetEateriesInSearchAreaAction();

        $this->assertCount(0, $eateries->eateries);
    }

    #[Test]
    public function itExposesTheIdsOfEveryEateryInTheSearchArea(): void
    {
        $eateries = $this->callGetEateriesInSearchAreaAction();

        $this->assertEqualsCanonicalizing(
            $eateries->eateries->map(fn (PendingEatery $eatery) => $eatery->id)->all(),
            SearchResultIdsState::$eateryIds,
        );
    }

    #[Test]
    public function theExposedIdsIgnoreTheAppliedFilters(): void
    {
        $unfiltered = $this->callGetEateriesInSearchAreaAction()->eateries;

        SearchResultIdsState::reset();

        $this->build(Eatery::class)->create([
            'type_id' => 2,
            'county_id' => $this->county->id,
            'town_id' => $this->town->id,
            'venue_type_id' => EateryVenueType::query()->first()->id,
            'lat' => 51.50,
            'lng' => -0.12,
        ]);

        $filtered = $this->callGetEateriesInSearchAreaAction(filters: ['categories' => ['att']]);

        $this->assertCount(1, $filtered->eateries);
        $this->assertCount($unfiltered->count() + 1, SearchResultIdsState::$eateryIds);
    }

    #[Test]
    public function itOnlyRunsTheRatingSubqueryWhenSortingByRating(): void
    {
        app('db')->enableQueryLog();

        $this->callGetEateriesInSearchAreaAction();

        $this->assertStringNotContainsString('from wheretoeat_reviews', $this->searchAreaQuery());
        $this->assertStringContainsString('0 as rating', $this->searchAreaQuery());

        app('db')->flushQueryLog();

        $this->callGetEateriesInSearchAreaAction(sort: 'rating');

        $this->assertStringContainsString('from wheretoeat_reviews', $this->searchAreaQuery());
    }

    #[Test]
    public function itDoesNotLoadTheCountyRelationToFilterOutNationwideEateries(): void
    {
        app('db')->enableQueryLog();

        $this->callGetEateriesInSearchAreaAction();

        $queries = collect(app('db')->getQueryLog())->pluck('query');

        $this->assertTrue(
            $queries->every(fn (string $query) => ! str_contains($query, 'from `wheretoeat_counties`')),
            'The county relation is still being loaded to reject nationwide eateries.',
        );
    }

    protected function searchAreaQuery(): string
    {
        return collect(app('db')->getQueryLog())
            ->pluck('query')
            ->first(fn (string $query) => str_contains($query, 'AS distance')) ?? '';
    }
}
