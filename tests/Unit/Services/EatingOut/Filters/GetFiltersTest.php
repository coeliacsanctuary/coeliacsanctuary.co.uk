<?php

declare(strict_types=1);

namespace Tests\Unit\Services\EatingOut\Filters;

use App\Enums\EatingOut\EateryType;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\EateryVenueType;
use App\Services\EatingOut\Filters\GetFilters;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class GetFiltersTest extends TestCase
{
    protected Collection $eateries;

    protected EateryCounty $county;

    protected EateryTown $town;

    protected EateryArea $area;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->county = EateryCounty::query()->withoutGlobalScopes()->first();
        $this->town = EateryTown::query()->withoutGlobalScopes()->first();
        $this->area = $this->create(EateryArea::class, ['town_id' => $this->town->id]);

        $this->eateries = $this->build(Eatery::class)
            ->count(5)
            ->create([
                'county_id' => $this->county->id,
                'town_id' => $this->town->id,
                'area_id' => $this->area->id,
                'venue_type_id' => EateryVenueType::query()->orderBy('venue_type')->first()->id,
            ]);

        EateryFeature::query()->orderBy('feature')->first()->eateries()->attach(Eatery::query()->first());

        $this->county->eateries->each(function (Eatery $eatery, $index): void {
            $this->build(EateryReview::class)
                ->count(5 - $index)
                ->create([
                    'wheretoeat_id' => $eatery->id,
                    'rating' => 5 - $index,
                    'approved' => true,
                ]);
        });
    }

    #[Test]
    public function itReturnsTheFiltersWithTheCorrectKeys(): void
    {
        $this->assertIsArray($this->getFilters());

        $keys = ['categories', 'venueTypes', 'features'];

        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $this->getFilters());
        }
    }

    #[Test]
    public function itReturnsTheEateryCategoryFilters(): void
    {
        $categoryFilters = $this->getFilters()['categories'];

        $keys = ['value', 'label', 'disabled', 'checked'];

        foreach ($categoryFilters as $category) {
            foreach ($keys as $key) {
                $this->assertArrayHasKey($key, $category);
            }
        }
    }

    #[Test]
    public function eachCategoryFilterIsNotCheckedByDefault(): void
    {
        $categoryFilters = $this->getFilters()['categories'];

        foreach ($categoryFilters as $category) {
            $this->assertFalse($category['checked']);
        }
    }

    #[Test]
    public function aFilterCategoryCanBeCheckedViaTheRequest(): void
    {
        Eatery::query()->first()->update(['type_id' => EateryType::ATTRACTION]);

        $categoryFilters = $this->getFilters(['categories' => 'att'])['categories'];

        $this->assertFalse($categoryFilters[0]['checked']); // wte
        $this->assertTrue($categoryFilters[1]['checked']); // att
    }

    #[Test]
    public function itReturnsTheEateryVenueTypeFilters(): void
    {
        $categoryFilters = $this->getFilters()['venueTypes'];

        $keys = ['value', 'label', 'disabled', 'checked'];

        foreach ($categoryFilters as $category) {
            foreach ($keys as $key) {
                $this->assertArrayHasKey($key, $category);
            }
        }
    }

    #[Test]
    public function eachVenueTypeFilterIsNotCheckedByDefault(): void
    {
        $categoryFilters = $this->getFilters()['venueTypes'];

        foreach ($categoryFilters as $category) {
            $this->assertFalse($category['checked']);
        }
    }

    #[Test]
    public function aFilterVenueTypeCanBeCheckedViaTheRequest(): void
    {
        $venueType = EateryVenueType::query()->orderBy('venue_type')->first();

        $categoryFilters = $this->getFilters(['venueTypes' => $venueType->slug])['venueTypes'];

        $this->assertTrue($categoryFilters[0]['checked']);
    }

    #[Test]
    public function itReturnsTheEateryFeaturesFilters(): void
    {
        $categoryFilters = $this->getFilters()['features'];

        $keys = ['value', 'label', 'disabled', 'checked'];

        foreach ($categoryFilters as $category) {
            foreach ($keys as $key) {
                $this->assertArrayHasKey($key, $category);
            }
        }
    }

    #[Test]
    public function eachFeatureFilterIsNotCheckedByDefault(): void
    {
        $featureFilters = $this->getFilters()['features'];

        foreach ($featureFilters as $feature) {
            $this->assertFalse($feature['checked']);
        }
    }

    #[Test]
    public function aFilterFeatureCanBeCheckedViaTheRequest(): void
    {
        $feature = EateryFeature::query()->orderBy('feature')->first();

        $featureFilters = $this->getFilters(['features' => $feature->slug])['features'];

        $this->assertTrue($featureFilters[0]['checked']);
    }

    protected function getFilters(array $filters = []): array
    {
        return $this->callAction(GetFilters::class, $filters);
    }
}
