<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryMagicRoutes\Pages;

use App\Filament\Resources\EatingOut\EateryMagicRoutes\Pages\ViewEateryMagicRoute;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryMagicRouteRecord;
use App\Models\User;
use App\Pipelines\EatingOut\GetEateries\GetEateriesForMagicRoutePipeline;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Bus;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ViewEateryMagicRouteTest extends TestCase
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
            'body' => ['page_intro' => 'Intro', 'meta_keywords' => ['gluten free', 'cheshire']],
        ]);

        $this->mock(GetEateriesForMagicRoutePipeline::class)
            ->shouldReceive('run')
            ->andReturn(collect([new Eatery(), new Eatery(), new Eatery()]));
    }

    #[Test]
    public function itShowsTheMagicRoute(): void
    {
        $this->viewPage()->assertSchemaStateSet([
            'id' => $this->routeRecord->id,
            'location' => 'England - Cheshire',
            'eateries' => 3,
        ], 'infolist');
    }

    #[Test]
    public function itJoinsArrayContentIntoASingleValue(): void
    {
        $this->viewPage()->assertSchemaStateSet([
            'body' => ['page_intro' => 'Intro', 'meta_keywords' => 'gluten free, cheshire'],
        ], 'infolist');
    }

    #[Test]
    public function itShowsTheConfiguration(): void
    {
        $this->viewPage()->assertSchemaComponentExists('builder_config', 'infolist')->assertSee('wheres');
    }

    #[Test]
    public function itCanBeRegeneratedButNotEditedOrDeleted(): void
    {
        $this->viewPage()
            ->assertActionVisible('regenerateMagicRouteBody')
            ->assertActionDoesNotExist('edit')
            ->assertActionDoesNotExist('delete');
    }

    protected function viewPage(): Testable
    {
        return Livewire::test(ViewEateryMagicRoute::class, ['record' => $this->routeRecord->id]);
    }
}
