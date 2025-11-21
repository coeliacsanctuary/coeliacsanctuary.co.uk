<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryAreas;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\EatingOut\EateryAreas\Pages\CreateEateryArea;
use App\Filament\Resources\EatingOut\EateryAreas\Pages\EditEateryArea;
use App\Filament\Resources\EatingOut\EateryAreas\Pages\ListEateryAreas;
use App\Filament\Resources\EatingOut\EateryAreas\RelationManagers\EateriesRelationManager;
use App\Filament\Resources\EatingOut\EateryAreas\RelationManagers\NationwideBranchesRelationManager;
use App\Filament\Resources\EatingOut\EateryAreas\Schemas\EateryAreaForm;
use App\Filament\Resources\EatingOut\EateryAreas\Tables\EateryAreasTable;
use App\Models\EatingOut\EateryArea;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class EateryAreaResource extends BaseResource
{
    protected static ?string $model = EateryArea::class;

    protected static ?string $recordTitleAttribute = 'area';

    protected static ?string $navigationLabel = 'London Areas';

    public static function form(Schema $schema): Schema
    {
        return EateryAreaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EateryAreasTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEateryAreas::route('/'),
            'create' => CreateEateryArea::route('/create'),
            'edit' => EditEateryArea::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            EateriesRelationManager::class,
            NationwideBranchesRelationManager::class,
        ];
    }
}
