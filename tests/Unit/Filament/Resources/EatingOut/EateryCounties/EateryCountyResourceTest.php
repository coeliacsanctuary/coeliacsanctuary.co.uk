<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryCounties;

use App\Filament\Resources\EatingOut\EateryCounties\EateryCountyResource;
use App\Filament\Resources\EatingOut\EateryCounties\RelationManagers\BoroughsRelationManager;
use App\Filament\Resources\EatingOut\EateryCounties\RelationManagers\TownsRelationManager;
use App\Models\User;
use Database\Seeders\EateryScaffoldingSeeder;
use Filament\Facades\Filament;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryCountyResourceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Bus::fake();

        Filament::setCurrentPanel('admin');

        $this->seed(EateryScaffoldingSeeder::class);

        $this->actingAs($this->create(User::class));
    }

    #[Test]
    public function itIncludesCountiesWithoutAnyPlaces(): void
    {
        $this->assertCount(1, EateryCountyResource::getEloquentQuery()->get());
    }

    #[Test]
    public function itTitlesACountyByItsName(): void
    {
        $this->assertSame('county', EateryCountyResource::getRecordTitleAttribute());
    }

    #[Test]
    public function itHasAMapIconInTheNavigation(): void
    {
        $this->assertSame(Heroicon::OutlinedMap, EateryCountyResource::getNavigationIcon());
    }

    #[Test]
    public function itRegistersTheListCreateViewAndEditPages(): void
    {
        $this->assertSame(['index', 'create', 'view', 'edit'], array_keys(EateryCountyResource::getPages()));
    }

    #[Test]
    public function itHasTownsAndBoroughsPanels(): void
    {
        $this->assertSame([TownsRelationManager::class, BoroughsRelationManager::class], EateryCountyResource::getRelations());
    }

    #[Test]
    public function itFindsCountiesWithoutAnyPlacesInTheGlobalSearch(): void
    {
        $this->assertCount(1, EateryCountyResource::getGlobalSearchResults('Cheshire'));
    }
}
