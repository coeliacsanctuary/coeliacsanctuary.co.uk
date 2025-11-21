<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\NationwideBranches;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\EatingOut\NationwideBranches\Pages\CreateNationwideBranch;
use App\Filament\Resources\EatingOut\NationwideBranches\Pages\EditNationwideBranch;
use App\Filament\Resources\EatingOut\NationwideBranches\Pages\ListNationwideBranches;
use App\Filament\Resources\EatingOut\NationwideBranches\Schemas\NationwideBranchForm;
use App\Filament\Resources\EatingOut\NationwideBranches\Tables\NationwideBranchesTable;
use App\Models\EatingOut\NationwideBranch;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NationwideBranchResource extends BaseResource
{
    protected static ?string $model = NationwideBranch::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return NationwideBranchForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return NationwideBranchesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'eatery' => fn ($query) => $query->withoutGlobalScopes(),
                'area' => fn ($query) => $query->withoutGlobalScopes(),
                'town' => fn ($query) => $query->withoutGlobalScopes(),
                'county' => fn ($query) => $query->withoutGlobalScopes(),
                'country' => fn ($query) => $query->withoutGlobalScopes(),
                'reviews' => fn ($query) => $query->withoutGlobalScopes(),
            ])
            ->withCount([
                'reviews' => fn ($query) => $query->withoutGlobalScopes(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNationwideBranches::route('/'),
            'create' => CreateNationwideBranch::route('/create'),
            'edit' => EditNationwideBranch::route('/{record}/edit'),
        ];
    }
}
