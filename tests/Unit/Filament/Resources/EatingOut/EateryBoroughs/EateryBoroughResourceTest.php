<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryBoroughs;

use App\Filament\Resources\EatingOut\EateryBoroughs\EateryBoroughResource;
use App\Filament\Resources\EatingOut\EateryBoroughs\RelationManagers\AreasRelationManager;
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

class EateryBoroughResourceTest extends TestCase
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
    public function itOnlyIncludesBoroughsIncludingThoseWithoutPlaces(): void
    {
        $borough = $this->create(EateryTown::class, ['town' => 'Camden', 'slug' => 'camden', 'county_id' => $this->london->id]);

        $this->assertTrue(EateryBoroughResource::getEloquentQuery()->sole()->is($borough));
    }

    #[Test]
    public function itOnlyFindsBoroughsInTheGlobalSearch(): void
    {
        $this->create(EateryTown::class, ['town' => 'Camden', 'slug' => 'camden', 'county_id' => $this->london->id]);

        $this->assertCount(1, EateryBoroughResource::getGlobalSearchResults('Camden'));
        $this->assertCount(0, EateryBoroughResource::getGlobalSearchResults('Crewe'));
    }

    #[Test]
    public function itResolvesRecordsByIdWhenTheSlugIsShared(): void
    {
        $first = $this->create(EateryTown::class, ['slug' => 'newport', 'county_id' => $this->london->id]);
        $second = $this->create(EateryTown::class, ['slug' => 'newport', 'county_id' => 1]);

        $this->assertTrue($first->is(EateryBoroughResource::resolveRecordRouteBinding($first->id)));
        $this->assertNull(EateryBoroughResource::resolveRecordRouteBinding($second->id));
        $this->assertStringEndsWith("/{$second->id}/edit", EateryBoroughResource::getUrl('edit', ['record' => $second]));
    }

    #[Test]
    public function itHasAnIconInTheNavigation(): void
    {
        $this->assertSame(Heroicon::OutlinedBuildingLibrary, EateryBoroughResource::getNavigationIcon());
    }

    #[Test]
    public function itRegistersTheListCreateAndEditPages(): void
    {
        $this->assertSame(['index', 'create', 'edit'], array_keys(EateryBoroughResource::getPages()));
    }

    #[Test]
    public function itRegistersItsPanels(): void
    {
        $this->assertSame([EateriesRelationManager::class, AreasRelationManager::class], EateryBoroughResource::getRelations());
    }
}
