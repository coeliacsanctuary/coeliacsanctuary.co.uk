<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryCounties;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\EatingOut\EateryCounties\Pages\CreateEateryCounty;
use App\Filament\Resources\EatingOut\EateryCounties\Pages\EditEateryCounty;
use App\Filament\Resources\EatingOut\EateryCounties\Pages\ListEateryCounties;
use App\Filament\Resources\EatingOut\EateryCounties\RelationManagers\EateriesRelationManager;
use App\Filament\Resources\EatingOut\EateryCounties\RelationManagers\NationwideBranchesRelationManager;
use App\Filament\Resources\EatingOut\EateryCounties\RelationManagers\TownsRelationManager;
use App\Filament\Resources\EatingOut\EateryCounties\Schemas\EateryCountyForm;
use App\Filament\Resources\EatingOut\EateryCounties\Tables\EateryCountiesTable;
use App\Models\EatingOut\EateryCounty;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class EateryCountyResource extends BaseResource
{
    protected static ?string $model = EateryCounty::class;

    protected static ?string $recordTitleAttribute = 'county';

    protected static ?string $navigationLabel = 'Counties';

    public static function form(Schema $schema): Schema
    {
        return EateryCountyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EateryCountiesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEateryCounties::route('/'),
            'create' => CreateEateryCounty::route('/create'),
            'edit' => EditEateryCounty::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            TownsRelationManager::class,
            EateriesRelationManager::class,
            NationwideBranchesRelationManager::class,
        ];
    }
}
