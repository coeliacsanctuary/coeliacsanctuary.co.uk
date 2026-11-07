<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\EatingOut\London\Borough\Area;

use App\Actions\OpenGraphImages\GetEatingOutOpenGraphImageAction;
use App\Jobs\OpenGraphImages\CreateEatingOutOpenGraphImageJob;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Pipelines\EatingOut\GetEateries\GetEateriesInLondonAreaPipeline;
use App\Services\EatingOut\Filters\GetFiltersForLondonArea;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Support\Facades\Bus;
use App\DataObjects\EatingOut\TripDestination;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ShowControllerTest extends TestCase
{
    protected EateryCounty $county;

    protected EateryTown $borough;

    protected EateryArea $area;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->county = EateryCounty::query()->withoutGlobalScopes()->first();
        $this->borough = EateryTown::query()->withoutGlobalScopes()->first();
        $this->area = $this->create(EateryArea::class, ['town_id' => $this->borough->id]);

        $this->county->update(['county' => 'London', 'slug' => 'london']);

        $this->create(Eatery::class, [
            'county_id' => $this->county->id,
            'town_id' => $this->borough->id,
            'area_id' => $this->area->id,
        ]);

        Bus::fake(CreateEatingOutOpenGraphImageJob::class);
    }

    #[Test]
    public function itReturnsNotFoundForATownThatDoesntExistWithAValidCounty(): void
    {
        $this->get(route('eating-out.london.borough.area', ['borough' => 'foo', 'area' => 'bar']))->assertNotFound();
    }

    #[Test]
    public function itReturnsNotFoundForAnAreaThatHasNoLiveEateries(): void
    {
        $area = $this->create(EateryArea::class, [
            'town_id' => $this->borough->id,
        ]);

        $this->get(route('eating-out.london.borough.area', ['borough' => $this->borough, 'area' => $area]))->assertNotFound();
    }

    #[Test]
    public function itReturnsOkForABoroughThatHasPlaces(): void
    {
        $this->visitArea()->assertOk();
    }

    #[Test]
    public function itCallsTheGetEateriesInTownAction(): void
    {
        $this->expectPipelineToRun(GetEateriesInLondonAreaPipeline::class);

        $this->visitArea();
    }

    #[Test]
    public function itCallsTheGetFiltersForTownService(): void
    {
        $this->expectAction(GetFiltersForLondonArea::class);

        $this->visitArea();
    }

    #[Test]
    public function itCallsTheGetOpenGraphImageAction(): void
    {
        $this->expectAction(GetEatingOutOpenGraphImageAction::class);

        $this->visitArea();
    }

    #[Test]
    public function itRendersTheInertiaPage(): void
    {
        $this->visitArea()
            ->assertInertia(
                fn (Assert $page): Assert => $page
                    ->component('EatingOut/LondonArea')
                    ->has('area')
                    ->where('area.name', $this->area->area)
                    ->etc()
            );
    }

    #[Test]
    public function itLinksToTheTripPlannerWithTheAreaAsTheDestination(): void
    {
        $this->area->updateQuietly(['latlng' => '54.4858,-0.6206']);

        $this->visitArea()->assertInertia(function (Assert $page): Assert {
            parse_str((string) parse_url((string) $page->toArray()['props']['area']['tripPlannerUrl'], PHP_URL_QUERY), $query);

            $this->assertSame(
                ['label' => "{$this->area->area}, London", 'lat' => 54.4858, 'lng' => -0.6206],
                TripDestination::fromQueryValue($query['to'])?->toArray(),
            );

            return $page;
        });
    }

    protected function visitArea(): TestResponse
    {
        return $this->get(route('eating-out.london.borough.area', ['borough' => $this->borough, 'area' => $this->area]));
    }
}
