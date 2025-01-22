<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\EatingOut\County\Town;

use PHPUnit\Framework\Attributes\Test;
use App\Actions\EatingOut\GetFiltersForEateriesAction;
use App\Actions\OpenGraphImages\GetEatingOutOpenGraphImageAction;
use App\Jobs\OpenGraphImages\CreateEatingOutOpenGraphImageJob;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Pipelines\EatingOut\GetEateries\GetEateriesPipeline;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Support\Facades\Bus;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ShowControllerTest extends TestCase
{
    protected EateryCounty $county;

    protected EateryTown $town;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->county = EateryCounty::query()->withoutGlobalScopes()->first();
        $this->town = EateryTown::query()->withoutGlobalScopes()->first();

        $this->create(Eatery::class);

        Bus::fake(CreateEatingOutOpenGraphImageJob::class);
    }

    #[Test]
    public function itReturnsNotFoundForACountyThatDoesntExistWithAValidTown(): void
    {
        $this->get(route('eating-out.town', ['county' => 'foo', 'town' => $this->town]))->assertNotFound();
    }

    #[Test]
    public function itReturnsNotFoundForATownThatDoesntExistWithAValidCounty(): void
    {
        $this->get(route('eating-out.town', ['county' => $this->county, 'town' => 'foo']))->assertNotFound();
    }

    #[Test]
    public function itReturnsNotFoundForATownThatHasNoLiveEateries(): void
    {
        $town = $this->create(EateryTown::class);

        $this->get(route('eating-out.town', ['county' => $this->county, 'town' => $town]))->assertNotFound();
    }

    #[Test]
    public function itReturnsOkForACountyThatHasPlaces(): void
    {
        $this->visitTown()->assertOk();
    }

    #[Test]
    public function itCallsTheGetEateriesInTownAction(): void
    {
        $this->expectPipelineToRun(GetEateriesPipeline::class);

        $this->visitTown();
    }

    #[Test]
    public function itCallsTheGetFiltersForTownAction(): void
    {
        $this->expectAction(GetFiltersForEateriesAction::class);

        $this->visitTown();
    }

    #[Test]
    public function itCallsTheGetOpenGraphImageAction(): void
    {
        $this->expectAction(GetEatingOutOpenGraphImageAction::class);

        $this->visitTown();
    }

    #[Test]
    public function itRendersTheInertiaPage(): void
    {
        $this->visitTown()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('EatingOut/Town')
                    ->has('town')
                    ->where('town.name', $this->town->town)
                    ->etc()
            );
    }

    protected function visitTown(): TestResponse
    {
        return $this->get(route('eating-out.town', ['county' => $this->county, 'town' => $this->town]));
    }
}
