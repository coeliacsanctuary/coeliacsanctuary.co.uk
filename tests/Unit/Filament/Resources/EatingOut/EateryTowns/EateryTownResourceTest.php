<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryTowns;

use App\Filament\Resources\EatingOut\EateryTowns\EateryTownResource;
use App\Filament\Resources\EatingOut\EateryTowns\RelationManagers\EateriesRelationManager;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Facades\Filament;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryTownResourceTest extends TestCase
{
    protected EateryCounty $london;

    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->london = $this->create(EateryCounty::class, ['county' => 'London', 'slug' => 'london']);
    }

    #[Test]
    public function itOnlyIncludesTownsOutsideOfLondonIncludingThoseWithoutPlaces(): void
    {
        $this->create(EateryTown::class, ['town' => 'Camden', 'slug' => 'camden', 'county_id' => $this->london->id]);

        $this->assertTrue(EateryTownResource::getEloquentQuery()->sole()->is(EateryTown::query()->withoutGlobalScopes()->findOrFail(1)));
    }

    #[Test]
    public function itOnlyFindsTownsInTheGlobalSearch(): void
    {
        $this->create(EateryTown::class, ['town' => 'Camden', 'slug' => 'camden', 'county_id' => $this->london->id]);

        $this->assertCount(0, EateryTownResource::getGlobalSearchResults('Camden'));
        $this->assertCount(1, EateryTownResource::getGlobalSearchResults('Crewe'));
    }

    #[Test]
    public function itResolvesRecordsByIdWhenTheSlugIsShared(): void
    {
        $first = $this->create(EateryTown::class, ['slug' => 'newport', 'county_id' => 1]);
        $second = $this->create(EateryTown::class, ['slug' => 'newport', 'county_id' => $this->create(EateryCounty::class)->id]);

        $this->assertTrue($first->is(EateryTownResource::resolveRecordRouteBinding($first->id)));
        $this->assertTrue($second->is(EateryTownResource::resolveRecordRouteBinding($second->id)));
        $this->assertStringEndsWith("/{$second->id}/edit", EateryTownResource::getUrl('edit', ['record' => $second]));
    }

    #[Test]
    public function itHasAnIconInTheNavigation(): void
    {
        $this->assertSame(Heroicon::OutlinedBuildingOffice2, EateryTownResource::getNavigationIcon());
    }

    #[Test]
    public function itRegistersTheListCreateAndEditPages(): void
    {
        $this->assertSame(['index', 'create', 'edit'], array_keys(EateryTownResource::getPages()));
    }

    #[Test]
    public function itRegistersItsPanels(): void
    {
        $this->assertSame([EateriesRelationManager::class], EateryTownResource::getRelations());
    }
}
