<?php

declare(strict_types=1);

namespace Tests\Feature\Resources\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryReview;
use App\ResourceCollections\EatingOut\NationwideListCollection;
use App\Resources\EatingOut\NationwidePageResource;
use Database\Seeders\EateryScaffoldingSeeder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class NationwidePageResourceTest extends TestCase
{
    protected EateryCounty $county;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->county = EateryCounty::query()->withoutGlobalScopes()->firstOrFail();

        $this->county->updateQuietly(['county' => 'Nationwide', 'slug' => 'nationwide']);
    }

    #[Test]
    public function itReturnsTheCorrectKeys(): void
    {
        $keys = ['name', 'slug', 'description', 'chains', 'eateries', 'reviews'];

        $resource = (new NationwidePageResource($this->county))->toArray(request());

        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $resource);
        }
    }

    #[Test]
    public function itFormatsTheDescriptionAsMarkdown(): void
    {
        $this->county->updateQuietly(['description' => 'Some **gluten free** chains']);

        $resource = (new NationwidePageResource($this->county))->toArray(request());

        $this->assertStringContainsString('<strong>gluten free</strong>', (string) $resource['description']);
    }

    #[Test]
    public function itFallsBackToTheDefaultDescriptionWhenTheCountyDoesntHaveOne(): void
    {
        $this->county->updateQuietly(['description' => null]);

        $resource = (new NationwidePageResource($this->county))->toArray(request());

        $this->assertNotEmpty((string) $resource['description']);
        $this->assertStringContainsString(
            'Discover nationwide chain restaurants',
            (string) $resource['description']
        );
    }

    #[Test]
    public function itReturnsTheChainsAsACollection(): void
    {
        $this->build(Eatery::class)
            ->count(3)
            ->create(['county_id' => $this->county->id]);

        $resource = (new NationwidePageResource($this->county))->toArray(request());

        $this->assertInstanceOf(NationwideListCollection::class, $resource['chains']);
    }

    #[Test]
    public function itListsTheNumberOfChains(): void
    {
        $this->build(Eatery::class)
            ->count(5)
            ->create(['county_id' => $this->county->id]);

        $resource = (new NationwidePageResource($this->county))->toArray(request());

        $this->assertEquals(5, $resource['eateries']);
    }

    #[Test]
    public function itFiltersTheChainsWhenGivenFilters(): void
    {
        $this->build(Eatery::class)->count(3)->create(['county_id' => $this->county->id]);
        $attraction = $this->build(Eatery::class)->attraction()->create(['county_id' => $this->county->id]);

        $resource = (new NationwidePageResource($this->county))
            ->withFilters(['categories' => ['att'], 'venueTypes' => null, 'features' => null])
            ->toArray(request());

        $this->assertCount(1, $resource['chains']);
        $this->assertEquals($attraction->id, $resource['chains']->first()->id);
    }

    #[Test]
    public function itStillListsTheUnfilteredNumberOfChainsWhenFiltering(): void
    {
        $this->build(Eatery::class)->count(3)->create(['county_id' => $this->county->id]);
        $this->build(Eatery::class)->attraction()->create(['county_id' => $this->county->id]);

        $resource = (new NationwidePageResource($this->county))
            ->withFilters(['categories' => ['att'], 'venueTypes' => null, 'features' => null])
            ->toArray(request());

        $this->assertEquals(4, $resource['eateries']);
    }

    #[Test]
    public function itListsTheNumberOfReviews(): void
    {
        $this->build(Eatery::class)
            ->count(5)
            ->has($this->build(EateryReview::class)->approved()->count(5), 'reviews')
            ->create(['county_id' => $this->county->id]);

        $resource = (new NationwidePageResource($this->county))->toArray(request());

        $this->assertEquals(25, $resource['reviews']);
    }
}
