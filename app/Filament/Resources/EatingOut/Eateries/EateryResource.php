<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\Eateries;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\EatingOut\Eateries\Pages\CreateEatery;
use App\Filament\Resources\EatingOut\Eateries\Pages\EditEatery;
use App\Filament\Resources\EatingOut\Eateries\Pages\ListEateries;
use App\Filament\Resources\EatingOut\Eateries\Schemas\EateryForm;
use App\Filament\Resources\EatingOut\Eateries\Tables\EateriesTable;
use App\Models\EatingOut\Eatery;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class EateryResource extends BaseResource
{
    protected static ?string $model = Eatery::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

    protected static string|UnitEnum|null $navigationGroup = 'Eating Out';

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
            ->with(['area', 'town', 'county', 'country', 'reviews'])
            ->withCount(['reviews']);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEateries::route('/'),
            'create' => CreateEatery::route('/create'),
            'edit' => EditEatery::route('/{record}/edit'),
        ];
    }
}
