<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryMagicRoutes\Actions;

use App\Ai\Agents\MagicRoutes\HundredPercentGlutenFreeMagicRouteContentAgent;
use App\DataObjects\EatingOut\GetEateriesPipelineData;
use App\Filament\Resources\EatingOut\EateryMagicRoutes\Pages\ListEateryMagicRoutes;
use App\Filament\Resources\EatingOut\EateryMagicRoutes\Pages\ViewEateryMagicRoute;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryMagicRouteRecord;
use App\Models\User;
use App\Pipelines\EatingOut\GetEateries\GetEateriesForMagicRoutePipeline;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Actions\Testing\TestAction;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Bus;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RegenerateMagicRouteBodyActionTest extends TestCase
{
    protected EateryMagicRouteRecord $routeRecord;

    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->create(Eatery::class, ['county_id' => 1, 'town_id' => 1, 'live' => true]);

        $this->routeRecord = $this->create(EateryMagicRouteRecord::class, [
            'raw_location' => 'cheshire',
            'location_type' => EateryCounty::class,
            'location_id' => 1,
            'body' => ['page_intro' => 'Old intro'],
        ]);

        $this->mock(GetEateriesForMagicRoutePipeline::class)
            ->shouldReceive('run')
            ->andReturn(collect())
            ->shouldReceive('rawData')
            ->andReturn(new GetEateriesPipelineData(filters: [], hydrated: collect()));

        HundredPercentGlutenFreeMagicRouteContentAgent::fake([
            ['page_intro' => 'New intro', 'meta_description' => 'New meta', 'meta_keywords' => ['gluten free']],
        ]);
    }

    #[Test]
    public function itRegeneratesTheBodyFromTheViewPage(): void
    {
        Livewire::test(ViewEateryMagicRoute::class, ['record' => $this->routeRecord->id])
            ->callAction('regenerateMagicRouteBody')
            ->assertNotified();

        $this->assertSame(
            ['page_intro' => 'New intro', 'meta_description' => 'New meta', 'meta_keywords' => ['gluten free']],
            $this->routeRecord->refresh()->body,
        );
    }

    #[Test]
    public function itRegeneratesTheBodyFromTheTable(): void
    {
        Livewire::test(ListEateryMagicRoutes::class)
            ->callAction(TestAction::make('regenerateMagicRouteBody')->table($this->routeRecord))
            ->assertNotified();

        $this->assertSame('New intro', $this->routeRecord->refresh()->body['page_intro']);
    }
}
