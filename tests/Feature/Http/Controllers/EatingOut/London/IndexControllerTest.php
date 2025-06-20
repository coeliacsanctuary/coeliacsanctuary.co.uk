<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\EatingOut\London;

use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryTown;
use PHPUnit\Framework\Attributes\Test;
use App\Actions\EatingOut\GetMostRatedPlacesInCountyAction;
use App\Actions\EatingOut\GetTopRatedPlacesInCountyAction;
use App\Actions\OpenGraphImages\GetEatingOutOpenGraphImageAction;
use App\Jobs\OpenGraphImages\CreateEatingOutOpenGraphImageJob;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
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
        $town = EateryTown::query()->withoutGlobalScopes()->first();

        $this->county->update(['slug' => 'london', 'county' => 'London']);

        $area = $this->build(EateryArea::class)->create([
            'town_id' => $town->id,
        ]);

        $this->build(Eatery::class)
            ->create([
                'county_id' => $this->county->id,
                'town_id' => $town->id,
                'area_id' => $area->id,
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

//    #[Test]
//    public function itCallsTheGetOpenGraphImageAction(): void
//    {
//        $this->expectAction(GetEatingOutOpenGraphImageAction::class);
//
//        $this->visitPage();
//    }

    #[Test]
    public function itRendersTheInertiaPage(): void
    {
        $this->visitPage()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('EatingOut/London')
                    ->has('london')
                    ->where('london.name', 'London')
                    ->etc()
            );
    }

    protected function visitPage(): TestResponse
    {
        return $this->get(route('eating-out.london'));
    }
}
