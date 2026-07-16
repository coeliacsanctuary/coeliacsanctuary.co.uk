<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\ResolvedFallbacks\HundredPercentGlutenFreeEateries;

use App\Actions\OpenGraphImages\GetEatingOutOpenGraphImageAction;
use App\Enums\EatingOut\EateryMagicRouteType;
use App\Jobs\OpenGraphImages\CreateEatingOutOpenGraphImageJob;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryMagicRouteRecord;
use App\Models\EatingOut\EateryTown;
use App\Pipelines\EatingOut\GetEateries\GetEateriesForMagicRoutePipeline;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Support\Facades\Bus;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CountyControllerTest extends TestCase
{
    protected EateryCounty $county;

    protected EateryTown $town;

    protected Eatery $eatery;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(EateryScaffoldingSeeder::class);

        $this->county = EateryCounty::query()->withoutGlobalScopes()->first();
        $this->town = EateryTown::query()->withoutGlobalScopes()->first();

        $this->eatery = $this->create(Eatery::class, ['county_id' => $this->county->id]);

        $this->create(EateryMagicRouteRecord::class, [
            'resolver_type' => EateryMagicRouteType::HundredPercentGlutenFree,
            'raw_location' => $this->county->slug,
            'location_type' => EateryCounty::class,
            'location_id' => $this->county->id,
            'body' => [
                'page_intro' => '## County Intro',
                'meta_description' => 'Some meta description',
                'meta_keywords' => ['gf', 'gluten-free'],
                $this->town->slug => 'Some town intro',
            ],
        ]);

        Bus::fake(CreateEatingOutOpenGraphImageJob::class);
    }

    #[Test]
    public function itReturnsOkForTheMagicRouteUrl(): void
    {
        $this->visitMagicRoute()->assertOk();
    }

    #[Test]
    public function itCallsThePipeline(): void
    {
        $this->expectPipelineToRun(GetEateriesForMagicRoutePipeline::class);

        $this->visitMagicRoute();
    }

    #[Test]
    public function itCallsTheGetOpenGraphImageAction(): void
    {
        $this->expectAction(GetEatingOutOpenGraphImageAction::class);

        $this->visitMagicRoute();
    }

    #[Test]
    public function itRendersTheInertiaPage(): void
    {
        $this->visitMagicRoute()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->component('EatingOut/MagicRoutes/HundredPercentGlutenFree/County')
                    ->has('county')
                    ->where('county.name', $this->county->county)
                    ->where('county.link', $this->county->link())
                    ->has('page_intro')
                    ->has('towns')
                    ->etc()
            );
    }

    #[Test]
    public function itGroupsTownsByTown(): void
    {
        $this->mock(GetEateriesForMagicRoutePipeline::class)
            ->shouldReceive('run')
            ->andReturn(collect([$this->eatery]));

        $this->visitMagicRoute()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has('towns', 1)
                    ->etc()
            );
    }

    protected function visitMagicRoute(): TestResponse
    {
        return $this->get('/eating-100-percent-gluten-free-in-' . $this->county->slug);
    }
}
