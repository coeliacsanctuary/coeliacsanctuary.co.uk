<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryTowns;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\EatingOut\EateryTowns\Pages\CreateEateryTown;
use App\Filament\Resources\EatingOut\EateryTowns\Pages\EditEateryTown;
use App\Filament\Resources\EatingOut\EateryTowns\Pages\ListEateryTowns;
use App\Filament\Resources\EatingOut\EateryTowns\RelationManagers\AreasRelationManager;
use App\Filament\Resources\EatingOut\EateryTowns\RelationManagers\EateriesRelationManager;
use App\Filament\Resources\EatingOut\EateryTowns\RelationManagers\NationwideBranchesRelationManager;
use App\Filament\Resources\EatingOut\EateryTowns\Schemas\EateryTownForm;
use App\Filament\Resources\EatingOut\EateryTowns\Tables\EateryTownsTable;
use App\Models\EatingOut\EateryTown;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class EateryTownResource extends BaseResource
{
    protected static ?string $model = EateryTown::class;

    protected static ?string $recordTitleAttribute = 'town';

    protected static ?string $navigationLabel = 'Towns';

    public static function form(Schema $schema): Schema
    {
        return EateryTownForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EateryTownsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEateryTowns::route('/'),
            'create' => CreateEateryTown::route('/create'),
            'edit' => EditEateryTown::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            EateriesRelationManager::class,
            NationwideBranchesRelationManager::class,
            AreasRelationManager::class,
        ];
    }
}
