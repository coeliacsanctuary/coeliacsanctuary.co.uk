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
use App\Pipelines\EatingOut\GetEateries\GetEateriesPipeline;
use App\Services\EatingOut\Filters\GetFiltersForLondonArea;
use App\Services\EatingOut\Filters\GetFiltersForTown;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Support\Facades\Bus;
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

//    #[Test]
//    public function itCallsTheGetOpenGraphImageAction(): void
//    {
//        $this->expectAction(GetEatingOutOpenGraphImageAction::class);
//
//        $this->visitTown();
//    }

    #[Test]
    public function itRendersTheInertiaPage(): void
    {
        $this->visitArea()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('EatingOut/LondonArea')
                    ->has('area')
                    ->where('area.name', $this->area->area)
                    ->etc()
            );
    }

    protected function visitArea(): TestResponse
    {
        return $this->get(route('eating-out.london.borough.area', ['borough' => $this->borough, 'area' => $this->area]));
    }
}
