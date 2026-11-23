<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\EatingOut\County;

use PHPUnit\Framework\Attributes\Test;
use App\Actions\EatingOut\GetMostRatedPlacesInCountyAction;
use App\Actions\EatingOut\GetTopRatedPlacesInCountyAction;
use App\Actions\OpenGraphImages\GetEatingOutOpenGraphImageAction;
use App\Jobs\OpenGraphImages\CreateEatingOutOpenGraphImageJob;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Support\Facades\Bus;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ShowControllerTest extends TestCase
{
    protected EateryCounty $county;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(EateryScaffoldingSeeder::class);

        $this->county = EateryCounty::query()->withoutGlobalScopes()->first();

        $this->build(Eatery::class)
            ->create([
                'county_id' => $this->county->id,
            ]);

        Bus::fake(CreateEatingOutOpenGraphImageJob::class);
    }

    #[Test]
    public function itReturnsNotFoundForACountyThatDoesntExist(): void
    {
        $this->get(route('eating-out.county', ['county' => 'foobar']))->assertNotFound();
    }

    #[Test]
    public function itReturnsNotFoundForACountyThatHasNoLiveEateries(): void
    {
        $county = $this->create(EateryCounty::class);

        $this->get(route('eating-out.county', ['county' => $county]))->assertNotFound();
    }

    #[Test]
    public function itReturnsOkForACountyThatHasPlaces(): void
    {
        $this->visitCounty()->assertOk();
    }

    #[Test]
    public function itReturnsOkForACountyThatJustHasNationwideBranches(): void
    {
        $this->county = $this->create(EateryCounty::class);
        $town = $this->create(EateryTown::class, ['county_id' => $this->county->id]);

        $this->create(NationwideBranch::class, [
            'wheretoeat_id' => $this->create(Eatery::class)->id,
            'town_id' => $town->id,
            'county_id' => $this->county->id,
        ]);

        $this->assertEmpty($town->liveEateries);
        $this->assertCount(1, $town->liveBranches);

        $this->visitCounty()->assertOk();
    }

    #[Test]
    public function itCallsTheGetMostRatedPlacesInCountyAction(): void
    {
        $this->expectAction(GetMostRatedPlacesInCountyAction::class);

        $this->visitCounty();
    }

    #[Test]
    public function itCallsTheGetTopRatedPlacesInCountyAction(): void
    {
        $this->expectAction(GetTopRatedPlacesInCountyAction::class);

        $this->visitCounty();
    }

    #[Test]
    public function itCallsTheGetOpenGraphImageAction(): void
    {
        $this->expectAction(GetEatingOutOpenGraphImageAction::class);

        $this->visitCounty();
    }

    #[Test]
    public function itRendersTheInertiaPage(): void
    {
        $this->visitCounty()
            ->assertInertia(
                fn (Assert $page): Assert => $page
                    ->component('EatingOut/County')
                    ->has('county')
                    ->where('county.name', $this->county->county)
                    ->etc()
            );
    }

    protected function visitCounty(): TestResponse
    {
        return $this->get(route('eating-out.county', ['county' => $this->county]));
    }
}
