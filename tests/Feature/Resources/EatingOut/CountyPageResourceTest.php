<?php

declare(strict_types=1);

namespace Tests\Feature\Resources\EatingOut;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EateryTown;
use App\ResourceCollections\EatingOut\CountyTownCollection;
use App\Resources\EatingOut\CountyPageResource;
use App\Resources\EatingOut\CountyTownResource;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CountyPageResourceTest extends TestCase
{
    protected EateryCounty $county;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(EateryScaffoldingSeeder::class);

        $this->county = EateryCounty::query()->withoutGlobalScopes()->first();

        Storage::fake('media');
    }

    #[Test]
    public function itReturnsTheCorrectKeys(): void
    {
        $keys = ['name', 'slug', 'image', 'towns', 'eateries', 'reviews'];

        $resource = (new CountyPageResource($this->county))->toArray(request());

        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $resource);
        }
    }

    #[Test]
    public function itUsesTheImageAssociatedWithTheCountyIfOneIsSet(): void
    {
        $this->county->addMedia(UploadedFile::fake()->image('county.jpg'))->toMediaCollection('primary');

        $resource = (new CountyPageResource($this->county))->toArray(request());

        $this->assertStringContainsString('county-webp.webp', $resource['image']);
    }

    #[Test]
    public function itFallsBackToTheGenericCountryImageIfTheCountyDoesntHaveOne(): void
    {
        $resource = (new CountyPageResource($this->county))->toArray(request());

        $this->assertStringContainsString('england.jpg', $resource['image']);
    }

    #[Test]
    public function itListsTheNumberOfEateriesInThatCounty(): void
    {
        $this->build(Eatery::class)
            ->count(5)
            ->create(['county_id' => $this->county->id]);

        $resource = (new CountyPageResource($this->county))->toArray(request());

        $this->assertEquals(5, $resource['eateries']);
    }

    #[Test]
    public function itListsTheNumberOfReviewsInTheCounty(): void
    {
        $this->build(Eatery::class)
            ->count(5)
            ->has($this->build(EateryReview::class)->approved()->count(5), 'reviews')
            ->create(['county_id' => $this->county->id]);

        $resource = (new CountyPageResource($this->county))->toArray(request());

        $this->assertEquals(25, $resource['reviews']);
    }

    #[Test]
    public function itReturnsTheTownsAsACollection(): void
    {
        $this->build(EateryTown::class)
            ->count(10)
            ->create(['county_id' => $this->county->id]);

        $resource = (new CountyPageResource($this->county))->toArray(request());

        $this->assertInstanceOf(CountyTownCollection::class, $resource['towns']);
    }

    #[Test]
    public function itReturnsEachTownAsATownResource(): void
    {
        $this->build(EateryTown::class)
            ->count(10)
            ->has($this->build(Eatery::class))
            ->create(['county_id' => $this->county->id]);

        /** @var CountyTownCollection $towns */
        $towns = (new CountyPageResource($this->county))->toArray(request())['towns'];

        $towns->resource->each(fn ($town) => $this->assertInstanceOf(CountyTownResource::class, $town));
    }
}
