<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\EatingOut\London\Borough;

use App\Actions\OpenGraphImages\GetEatingOutOpenGraphImageAction;
use App\Jobs\OpenGraphImages\CreateEatingOutOpenGraphImageJob;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->county = EateryCounty::query()->withoutGlobalScopes()->first();
        $this->borough = EateryTown::query()->withoutGlobalScopes()->first();
        $area = $this->create(EateryArea::class, ['town_id' => $this->borough->id]);

        $this->county->update(['county' => 'London', 'slug' => 'london']);

        $this->create(Eatery::class, [
            'county_id' => $this->county->id,
            'town_id' => $this->borough->id,
            'area_id' => $area->id,
        ]);

        Bus::fake(CreateEatingOutOpenGraphImageJob::class);
    }

    #[Test]
    public function itReturnsNotFoundForATownThatDoesntExistWithAValidCounty(): void
    {
        $this->get(route('eating-out.london.borough', ['borough' => 'foo']))->assertNotFound();
    }

    #[Test]
    public function itReturnsNotFoundForATownThatHasNoLiveEateries(): void
    {
        $borough = $this->create(EateryTown::class, [
            'county_id' => $this->county->id,
        ]);

        $this->get(route('eating-out.london.borough', [$borough]))->assertNotFound();
    }

    #[Test]
    public function itReturnsOkForABoroughThatHasPlaces(): void
    {
        $this->visitBorough()->assertOk();
    }

    #[Test]
    public function itCallsTheGetOpenGraphImageAction(): void
    {
        $this->expectAction(GetEatingOutOpenGraphImageAction::class);

        $this->visitBorough();
    }

    #[Test]
    public function itRendersTheInertiaPage(): void
    {
        $this->visitBorough()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('EatingOut/LondonBorough')
                    ->has('borough')
                    ->where('borough.name', $this->borough->town)
                    ->etc()
            );
    }

    protected function visitBorough(): TestResponse
    {
        return $this->get(route('eating-out.london.borough', $this->borough));
    }
}
