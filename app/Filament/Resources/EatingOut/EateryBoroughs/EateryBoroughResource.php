<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryBoroughs;

use App\Filament\Resources\EatingOut\EateryBoroughs\Pages\CreateEateryBorough;
use App\Filament\Resources\EatingOut\EateryBoroughs\Pages\EditEateryBorough;
use App\Filament\Resources\EatingOut\EateryBoroughs\Pages\ListEateryBoroughs;
use App\Filament\Resources\EatingOut\EateryBoroughs\RelationManagers\AreasRelationManager;
use App\Filament\Resources\EatingOut\EateryTowns\EateryTownResource;
use App\Filament\Resources\EatingOut\EateryTowns\RelationManagers\EateriesRelationManager;
use App\Filament\Resources\EatingOut\EateryTowns\Schemas\EateryTownForm;
use App\Filament\Resources\EatingOut\EateryTowns\Tables\EateryTownsTable;
use App\Models\EatingOut\EateryCounty;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EateryBoroughResource extends EateryTownResource
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingLibrary;

    protected static ?string $modelLabel = 'borough';

    protected static ?string $navigationLabel = 'London Boroughs';

    protected static function constrainCounty(Builder $query): Builder
    {
        return $query->where('slug', 'london');
    }

    public static function londonId(): int
    {
        return EateryCounty::query()->withoutGlobalScopes()->where('slug', 'london')->firstOrFail()->id;
    }

    public static function form(Schema $schema): Schema
    {
        return EateryTownForm::configure($schema, isBorough: true);
    }

    public static function table(Table $table): Table
    {
        return EateryTownsTable::configure($table, isBorough: true);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEateryBoroughs::route('/'),
            'create' => CreateEateryBorough::route('/create'),
            'edit' => EditEateryBorough::route('/{record:id}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            EateriesRelationManager::class,
            AreasRelationManager::class,
        ];
    }
}
