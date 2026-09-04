<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\Eateries;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\EatingOut\Eateries\Pages\CreateEatery;
use App\Filament\Resources\EatingOut\Eateries\Pages\EditEatery;
use App\Filament\Resources\EatingOut\Eateries\Pages\ListEateries;
use App\Filament\Resources\EatingOut\Eateries\RelationManagers\BranchesRelationManager;
use App\Filament\Resources\EatingOut\Eateries\RelationManagers\ReviewsRelationManager;
use App\Filament\Resources\EatingOut\Eateries\Schemas\EateryForm;
use App\Filament\Resources\EatingOut\Eateries\Tables\EateriesTable;
use App\Models\EatingOut\Eatery;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EateryResource extends BaseResource
{
    protected static ?string $model = Eatery::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return EateryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EateriesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'area' => fn ($query) => $query->withoutGlobalScopes(),
                'town' => fn ($query) => $query->withoutGlobalScopes(),
                'county' => fn ($query) => $query->withoutGlobalScopes(),
                'country' => fn ($query) => $query->withoutGlobalScopes(),
                'reviews' => fn ($query) => $query->withoutGlobalScopes(),
            ])
            ->withCount([
                'nationwideBranches' => fn ($query) => $query->withoutGlobalScopes(),
                'reviews' => fn ($query) => $query->withoutGlobalScopes(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEateries::route('/'),
            'create' => CreateEatery::route('/create'),
            'edit' => EditEatery::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            ReviewsRelationManager::class,
            BranchesRelationManager::class,
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'town.town', 'county.county', 'area.area'];
    }
}
