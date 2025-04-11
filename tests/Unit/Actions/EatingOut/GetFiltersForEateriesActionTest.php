<?php

declare(strict_types=1);

namespace Tests\Unit\Actions\EatingOut;

use App\Enums\EatingOut\EateryType;
use PHPUnit\Framework\Attributes\Test;
use App\Actions\EatingOut\GetFiltersForEateriesAction;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EateryReview;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\EateryVenueType;
use Closure;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Database\Eloquent\Builder;
use Tests\TestCase;

class GetFiltersForEateriesActionTest extends TestCase
{
    protected EateryCounty $county;

    protected EateryTown $town;

    protected Closure $whereClause;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->county = EateryCounty::query()->withoutGlobalScopes()->first();
        $this->town = EateryTown::query()->withoutGlobalScopes()->first();
        $this->whereClause = fn (Builder $query) => $query->where('town_id', $this->town->id);

        $this->build(Eatery::class)
            ->count(5)
            ->create([
                'county_id' => $this->county->id,
                'town_id' => $this->town->id,
                'venue_type_id' => EateryVenueType::query()->first()->id,
            ]);

        EateryFeature::query()->first()->eateries()->attach(Eatery::query()->first());

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
        $this->assertIsArray($this->callAction(GetFiltersForEateriesAction::class, $this->whereClause));

        $keys = ['categories', 'venueTypes', 'features'];

        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $this->callAction(GetFiltersForEateriesAction::class, $this->whereClause));
        }
    }

    #[Test]
    public function itReturnsTheEateryCategoryFilters(): void
    {
        $categoryFilters = $this->callAction(GetFiltersForEateriesAction::class, $this->whereClause)['categories'];

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
        $categoryFilters = $this->callAction(GetFiltersForEateriesAction::class, $this->whereClause)['categories'];

        foreach ($categoryFilters as $category) {
            $this->assertFalse($category['checked']);
        }
    }

    #[Test]
    public function aFilterCategoryCanBeCheckedViaTheRequest(): void
    {
        Eatery::query()->first()->update(['type_id' => EateryType::ATTRACTION]);

        $categoryFilters = $this->callAction(GetFiltersForEateriesAction::class, $this->whereClause, ['categories' => 'att'])['categories'];

        $this->assertFalse($categoryFilters[0]['checked']); // wte
        $this->assertTrue($categoryFilters[1]['checked']); // att
    }

    #[Test]
    public function itReturnsTheEateryVenueTypeFilters(): void
    {
        $categoryFilters = $this->callAction(GetFiltersForEateriesAction::class, $this->whereClause)['venueTypes'];

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
        $categoryFilters = $this->callAction(GetFiltersForEateriesAction::class, $this->whereClause)['venueTypes'];

        foreach ($categoryFilters as $category) {
            $this->assertFalse($category['checked']);
        }
    }

    #[Test]
    public function aFilterVenueTypeCanBeCheckedViaTheRequest(): void
    {
        $venueType = EateryVenueType::query()->first();

        $categoryFilters = $this->callAction(GetFiltersForEateriesAction::class, $this->whereClause, ['venueTypes' => $venueType->slug])['venueTypes'];

        $this->assertTrue($categoryFilters[0]['checked']);
    }

    #[Test]
    public function itReturnsTheEateryFeaturesFilters(): void
    {
        $categoryFilters = $this->callAction(GetFiltersForEateriesAction::class, $this->whereClause)['features'];

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
        $featureFilters = $this->callAction(GetFiltersForEateriesAction::class, $this->whereClause)['features'];

        foreach ($featureFilters as $feature) {
            $this->assertFalse($feature['checked']);
        }
    }

    #[Test]
    public function aFilterFeatureCanBeCheckedViaTheRequest(): void
    {
        $feature = EateryFeature::query()->first();

        $featureFilters = $this->callAction(GetFiltersForEateriesAction::class, $this->whereClause, ['features' => $feature->slug])['features'];

        $this->assertTrue($featureFilters[0]['checked']);
    }
}
