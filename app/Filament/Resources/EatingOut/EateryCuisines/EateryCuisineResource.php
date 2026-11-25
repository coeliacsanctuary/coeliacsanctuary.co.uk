<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryCuisines;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\EatingOut\EateryCuisines\Pages\EditEateryCuisine;
use App\Filament\Resources\EatingOut\EateryCuisines\Pages\ListEateryCuisines;
use App\Filament\Resources\EatingOut\EateryCuisines\RelationManagers\EateriesRelationManager;
use App\Filament\Resources\EatingOut\EateryCuisines\Schemas\EateryCuisineForm;
use App\Filament\Resources\EatingOut\EateryCuisines\Tables\EateryCuisinesTable;
use App\Models\EatingOut\EateryCuisine;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EateryCuisineResource extends BaseResource
{
    protected static ?string $model = EateryCuisine::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFire;

    protected static ?string $recordTitleAttribute = 'cuisine';

    protected static ?string $modelLabel = 'cuisine';

    protected static ?string $navigationLabel = 'Cuisines';

    public static function form(Schema $schema): Schema
    {
        return EateryCuisineForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return EateryCuisinesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEateryCuisines::route('/'),
            'edit' => EditEateryCuisine::route('/{record}/edit'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            EateriesRelationManager::class,
        ];
    }
}
