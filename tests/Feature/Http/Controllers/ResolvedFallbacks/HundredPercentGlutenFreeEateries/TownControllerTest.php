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
use App\Support\MagicRouting\Resolvers\HundredPercentGlutenFreeEateriesFallbackResolver;
use Database\Seeders\EateryScaffoldingSeeder;
use Illuminate\Support\Facades\Bus;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia as Assert;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TownControllerTest extends TestCase
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
        $this->town->update(['slug' => 'crewe']);

        $this->eatery = $this->create(Eatery::class, [
            'county_id' => $this->county->id,
            'town_id' => $this->town->id,
        ]);

        $this->create(EateryMagicRouteRecord::class, [
            'resolver_type' => EateryMagicRouteType::HundredPercentGlutenFree,
            'raw_location' => $this->town->slug,
            'location_type' => EateryTown::class,
            'location_id' => $this->town->id,
            'body' => [
                'page_intro' => '## Town Intro',
                'meta_description' => 'Some meta description',
                'meta_keywords' => ['gf', 'gluten-free'],
                'outro' => '## Town Outro',
                $this->eatery->slug => 'Some eatery info',
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
                    ->component('EatingOut/MagicRoutes/HundredPercentGlutenFree/Town')
                    ->has('town')
                    ->where('town.name', $this->town->town)
                    ->where('town.link', $this->town->link())
                    ->has('page_intro')
                    ->has('page_outro')
                    ->has('eateries')
                    ->etc()
            );
    }

    #[Test]
    public function itMapsEateriesToFlatList(): void
    {
        $this->mock(GetEateriesForMagicRoutePipeline::class)
            ->shouldReceive('run')
            ->andReturn(collect([$this->eatery]));

        $this->visitMagicRoute()
            ->assertInertia(
                fn (Assert $page) => $page
                    ->has('eateries', 1)
                    ->etc()
            );
    }

    protected function visitMagicRoute(): TestResponse
    {
        $path = app(HundredPercentGlutenFreeEateriesFallbackResolver::class)->generateRoutePath([
            'location' => $this->town->slug,
        ]);

        return $this->get($path);
    }
}
