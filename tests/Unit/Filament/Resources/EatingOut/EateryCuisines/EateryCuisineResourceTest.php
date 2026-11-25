<?php

declare(strict_types=1);

namespace Tests\Unit\Filament\Resources\EatingOut\EateryCuisines;

use App\Filament\Resources\EatingOut\EateryCuisines\EateryCuisineResource;
use App\Filament\Resources\EatingOut\EateryCuisines\RelationManagers\EateriesRelationManager;
use Filament\Support\Icons\Heroicon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EateryCuisineResourceTest extends TestCase
{
    #[Test]
    public function itHasAnIconInTheNavigation(): void
    {
        $this->assertSame(Heroicon::OutlinedFire, EateryCuisineResource::getNavigationIcon());
    }

    #[Test]
    public function itRegistersTheListAndEditPages(): void
    {
        $this->assertSame(['index', 'edit'], array_keys(EateryCuisineResource::getPages()));
    }

    #[Test]
    public function itRegistersItsPanels(): void
    {
        $this->assertSame([EateriesRelationManager::class], EateryCuisineResource::getRelations());
    }
}
