<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\EatingOut\Nationwide;

use PHPUnit\Framework\Attributes\Test;
use App\Actions\EatingOut\GetMostRatedPlacesInCountyAction;
use App\Actions\EatingOut\GetTopRatedPlacesInCountyAction;
use App\Actions\OpenGraphImages\GetEatingOutOpenGraphImageAction;
use App\Jobs\OpenGraphImages\CreateEatingOutOpenGraphImageJob;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryVenueType;
use App\Services\EatingOut\Filters\GetFiltersForNationwide;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Support\Facades\Bus;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class IndexControllerTest extends TestCase
{
    protected EateryCounty $county;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(EateryScaffoldingSeeder::class);

        $this->county = EateryCounty::query()->withoutGlobalScopes()->first();

        $this->county->update(['slug' => 'nationwide']);

        $this->build(Eatery::class)
            ->create([
                'county_id' => $this->county->id,
            ]);

        Bus::fake(CreateEatingOutOpenGraphImageJob::class);
    }

    #[Test]
    public function itReturnsOk(): void
    {
        $this->visitPage()->assertOk();
    }

    #[Test]
    public function itCallsTheGetMostRatedPlacesInCountyAction(): void
    {
        $this->expectAction(GetMostRatedPlacesInCountyAction::class);

        $this->visitPage();
    }

    #[Test]
    public function itCallsTheGetTopRatedPlacesInCountyAction(): void
    {
        $this->expectAction(GetTopRatedPlacesInCountyAction::class);

        $this->visitPage();
    }

    #[Test]
    public function itCallsTheGetOpenGraphImageAction(): void
    {
        $this->expectAction(GetEatingOutOpenGraphImageAction::class);

        $this->visitPage();
    }

    #[Test]
    public function itCallsTheGetFiltersForNationwideService(): void
    {
        $this->expectAction(GetFiltersForNationwide::class);

        $this->visitPage();
    }

    #[Test]
    public function itRendersTheInertiaPage(): void
    {
        $this->visitPage()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('EatingOut/Nationwide')
                    ->has('county')
                    ->where('county.name', $this->county->county)
                    ->etc()
            );
    }

    #[Test]
    public function itRendersTheFiltersOnThePage(): void
    {
        $this->visitPage()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has('filters.categories')
                    ->has('filters.venueTypes')
                    ->has('filters.features')
                    ->etc()
            );
    }

    #[Test]
    public function itReturnsAllOfTheChainsWhenNoFiltersAreGiven(): void
    {
        $this->build(Eatery::class)->attraction()->create(['county_id' => $this->county->id]);

        $this->visitPage()->assertInertia(fn (Assert $page) => $page->has('county.chains', 2)->etc());
    }

    #[Test]
    public function itFiltersTheChainsByCategory(): void
    {
        $attraction = $this->build(Eatery::class)->attraction()->create(['county_id' => $this->county->id]);

        $this->visitPage(['filter' => ['category' => 'att']])
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has('county.chains', 1)
                    ->where('county.chains.0.key', $attraction->id)
                    ->etc()
            );
    }

    #[Test]
    public function itFiltersTheChainsByVenueType(): void
    {
        $venueType = EateryVenueType::query()->where('id', 2)->firstOrFail();

        $eatery = $this->build(Eatery::class)->create([
            'county_id' => $this->county->id,
            'venue_type_id' => $venueType->id,
        ]);

        $this->visitPage(['filter' => ['venueType' => $venueType->slug]])
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has('county.chains', 1)
                    ->where('county.chains.0.key', $eatery->id)
                    ->etc()
            );
    }

    #[Test]
    public function itFiltersTheChainsByFeature(): void
    {
        $feature = EateryFeature::query()->orderBy('feature')->firstOrFail();

        $eatery = $this->build(Eatery::class)->create(['county_id' => $this->county->id]);

        $feature->eateries()->attach($eatery);

        $this->visitPage(['filter' => ['feature' => $feature->slug]])
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has('county.chains', 1)
                    ->where('county.chains.0.key', $eatery->id)
                    ->etc()
            );
    }

    #[Test]
    public function itStillReportsTheUnfilteredNumberOfChainsWhenFiltering(): void
    {
        $this->build(Eatery::class)->attraction()->create(['county_id' => $this->county->id]);

        $this->visitPage(['filter' => ['category' => 'att']])
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has('county.chains', 1)
                    ->where('county.eateries', 2)
                    ->etc()
            );
    }

    protected function visitPage(array $params = []): TestResponse
    {
        return $this->get(route('eating-out.nationwide', $params));
    }
}
