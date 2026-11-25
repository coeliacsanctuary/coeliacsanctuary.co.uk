<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryAreas;

use App\Filament\Resources\EatingOut\EateryAreas\EateryAreaResource;
use App\Filament\Resources\EatingOut\EateryAreas\RelationManagers\EateriesRelationManager;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Facades\Filament;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryAreaResourceTest extends TestCase
{
    protected EateryCounty $london;

    protected EateryTown $borough;

    protected EateryArea $area;

    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));

        $this->london = $this->create(EateryCounty::class, ['county' => 'London', 'slug' => 'london', 'country_id' => 1]);
        $this->borough = $this->create(EateryTown::class, ['town' => 'Camden', 'slug' => 'camden', 'county_id' => $this->london->id]);
        $this->area = $this->create(EateryArea::class, ['area' => 'Holborn', 'slug' => 'holborn', 'latlng' => '51.5,-0.1', 'town_id' => $this->borough->id]);
    }

    #[Test]
    public function itIncludesAreasWithoutPlaces(): void
    {
        $this->assertTrue(EateryAreaResource::getEloquentQuery()->sole()->is($this->area));
    }

    #[Test]
    public function itResolvesRecordsByIdWhenTheSlugIsShared(): void
    {
        $other = $this->create(EateryArea::class, [
            'slug' => 'holborn',
            'town_id' => $this->create(EateryTown::class, ['county_id' => $this->london->id])->id,
        ]);

        $this->assertTrue($this->area->is(EateryAreaResource::resolveRecordRouteBinding($this->area->id)));
        $this->assertTrue($other->is(EateryAreaResource::resolveRecordRouteBinding($other->id)));
        $this->assertStringEndsWith("/{$other->id}/edit", EateryAreaResource::getUrl('edit', ['record' => $other]));
    }

    #[Test]
    public function itHasAnIconInTheNavigation(): void
    {
        $this->assertSame(Heroicon::OutlinedMapPin, EateryAreaResource::getNavigationIcon());
    }

    #[Test]
    public function itRegistersTheListCreateAndEditPages(): void
    {
        $this->assertSame(['index', 'create', 'edit'], array_keys(EateryAreaResource::getPages()));
    }

    #[Test]
    public function itRegistersItsPanels(): void
    {
        $this->assertSame([EateriesRelationManager::class], EateryAreaResource::getRelations());
    }
}
