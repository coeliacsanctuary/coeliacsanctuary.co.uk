<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\EatingOut\Search;

use App\Actions\OpenGraphImages\GetOpenGraphImageForRouteAction;
use App\DataObjects\EatingOut\LatLng;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EaterySearchTerm;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use App\Pipelines\EatingOut\GetEateries\GetSearchResultsPipeline;
use App\Resources\EatingOut\EateryListResource;
use App\Services\EatingOut\Filters\GetFiltersForSearchResults;
use App\Services\EatingOut\LocationSearchService;
use App\Support\State\EatingOut\Search\LatLngState;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShowControllerTest extends TestCase
{
    protected EaterySearchTerm $eaterySearchTerm;

    protected function setUp(): void
    {
        parent::setUp();

        $london = ['lat' => 51.5, 'lng' => -0.1];

        $this->mock(LocationSearchService::class)
            ->expects('getLatLng')
            ->zeroOrMoreTimes()
            ->andReturn(new LatLng($london['lat'], $london['lng']));

        $this->seed(EateryScaffoldingSeeder::class);

        $this->county = EateryCounty::query()->withoutGlobalScopes()->first();

        $this->create(Eatery::class);

        $this->eaterySearchTerm = $this->create(EaterySearchTerm::class, ['term' => 'London']);
    }

    #[Test]
    public function itReturnsNotFoundForASearchTermThatDoesntExist(): void
    {
        $this->get(route('eating-out.search.show', ['eaterySearchTerm' => 'foo']))->assertNotFound();
    }

    protected function visitSearchResults(): TestResponse
    {
        return $this->get(route('eating-out.search.show', ['eaterySearchTerm' => $this->eaterySearchTerm->key]));
    }

    #[Test]
    public function itReturnsOk(): void
    {
        $this->visitSearchResults()->assertOk();
    }

    #[Test]
    public function itCallsTheGetEateriesInSearchAreaAction(): void
    {
        $this->expectPipelineToRun(GetSearchResultsPipeline::class);

        $this->visitSearchResults();
    }

    #[Test]
    public function itCallsTheGetFiltersForSearchResultsAction(): void
    {
        $this->expectAction(GetFiltersForSearchResults::class);

        $this->visitSearchResults();
    }

    #[Test]
    public function itCallsTheGetOpenGraphImageForRouteAction(): void
    {
        $this->expectAction(GetOpenGraphImageForRouteAction::class, ['eatery']);

        $this->visitSearchResults();
    }

    #[Test]
    public function itRendersTheInertiaPage(): void
    {
        $this->visitSearchResults()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('EatingOut/SearchResults')
                    ->etc()
            );
    }

    #[Test]
    public function itUsesTheBranchLocationWhenTheFirstResultIsANationwideBranch(): void
    {
        $nationwide = $this->create(EateryCountry::class, ['country' => 'Nationwide']);
        $nationwideCounty = $this->create(EateryCounty::class, ['country_id' => $nationwide->id, 'county' => 'Nationwide']);
        $nationwideTown = $this->create(EateryTown::class, ['county_id' => $nationwideCounty->id, 'town' => 'Nationwide']);

        $wales = $this->create(EateryCountry::class, ['country' => 'Wales']);
        $county = EateryCounty::query()->withoutGlobalScopes()->firstOrFail();
        $town = EateryTown::query()->withoutGlobalScopes()->firstOrFail();

        $chain = $this->create(Eatery::class, [
            'country_id' => $nationwide->id,
            'county_id' => $nationwideCounty->id,
            'town_id' => $nationwideTown->id,
        ]);

        $branch = $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $chain->id,
            'country_id' => $wales->id,
            'county_id' => $county->id,
            'town_id' => $town->id,
        ]);

        $chain->setRelation('branch', $branch);

        $this->expectPipelineToRun(GetSearchResultsPipeline::class, $this->resultsFor($chain));

        $this->visitSearchResults()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->where('relatedPage.name', $town->town)
                    ->where('relatedPage.link', $town->link())
                    ->where('latlng.lat', $branch->lat)
                    ->where('latlng.lng', $branch->lng)
                    ->etc()
            );
    }

    #[Test]
    public function itFlagsThatTheLocationWasFound(): void
    {
        $this->visitSearchResults()
            ->assertInertia(fn (Assert $page) => $page->where('locationFound', true)->etc());
    }

    #[Test]
    public function itFlagsThatTheLocationWasNotFoundWhenItCouldNotBeGeocoded(): void
    {
        LatLngState::$latLng = null;

        $this->expectPipelineToRun(GetSearchResultsPipeline::class, $this->resultsFor());

        $this->visitSearchResults()
            ->assertInertia(fn (Assert $page) => $page->where('locationFound', false)->etc());
    }

    #[Test]
    public function itPointsAtTheCountyPageWhenTheTermIsACounty(): void
    {
        $county = EateryCounty::query()->withoutGlobalScopes()->firstOrFail();

        $searchTerm = $this->create(EaterySearchTerm::class, ['term' => $county->county]);

        $this->get(route('eating-out.search.show', ['eaterySearchTerm' => $searchTerm->key]))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->where('relatedPage.name', $county->county)
                    ->where('relatedPage.link', $county->link())
                    ->etc()
            );
    }

    #[Test]
    public function itPointsAtTheTownOfTheFirstResultWhenTheTermIsNotACounty(): void
    {
        $eatery = Eatery::query()->firstOrFail();

        $this->expectPipelineToRun(GetSearchResultsPipeline::class, $this->resultsFor($eatery));

        $this->visitSearchResults()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->where('relatedPage.name', $eatery->town->town)
                    ->where('relatedPage.link', $eatery->town->link())
                    ->etc()
            );
    }

    #[Test]
    public function itHasNoRelatedPageWithNoResultsAndANonCountyTerm(): void
    {
        $this->expectPipelineToRun(GetSearchResultsPipeline::class, $this->resultsFor());

        $this->visitSearchResults()
            ->assertInertia(fn (Assert $page) => $page->where('relatedPage', null)->etc());
    }

    #[Test]
    public function itFormatsThePostcodeTermForDisplay(): void
    {
        $searchTerm = $this->create(EaterySearchTerm::class, ['term' => 'cw1 2ab']);

        $this->get(route('eating-out.search.show', ['eaterySearchTerm' => $searchTerm->key]))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->where('term', 'CW1 2AB')
                    ->where('prefillTerm', 'CW1 2AB')
                    ->etc()
            );
    }

    #[Test]
    public function itDescribesASearchFromTheUsersLocation(): void
    {
        $searchTerm = $this->create(EaterySearchTerm::class, [
            'term' => '53.0873,-2.4419',
            'from_user_location' => true,
        ]);

        $this->get(route('eating-out.search.show', ['eaterySearchTerm' => $searchTerm->key]))
            ->assertInertia(
                fn (Assert $page) => $page
                    ->where('term', 'your location')
                    ->where('prefillTerm', '')
                    ->etc()
            );
    }

    /** @return LengthAwarePaginator<int, EateryListResource> */
    protected function resultsFor(?Eatery $eatery = null): LengthAwarePaginator
    {
        $items = $eatery ? [new EateryListResource($eatery)] : [];

        return new LengthAwarePaginator($items, count($items), 10);
    }
}
