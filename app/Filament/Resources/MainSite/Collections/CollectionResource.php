<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Collections;

use App\Filament\Resources\CollectionResource\Pages;
use App\Filament\Resources\MainSite\Collections\RelationManagers\ItemsRelationManager;
use App\Filament\Resources\MainSite\Collections\Schemas\CollectionForm;
use App\Filament\Resources\MainSite\Collections\Tables\CollectionsTable;
use App\Filament\Transformers\StatusTransformer;
use App\Models\Collections\Collection;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class CollectionResource extends Resource
{
    protected static ?string $model = Collection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 3;

    protected static string|UnitEnum|null $navigationGroup = 'Main Site';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->reorder('id', 'desc');
    }

    public static function form(Schema $schema): Schema
    {
        return CollectionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CollectionsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\MainSite\Collections\Pages\ListCollections::route('/'),
            'create' => \App\Filament\Resources\MainSite\Collections\Pages\CreateCollection::route('/create'),
            'edit' => \App\Filament\Resources\MainSite\Collections\Pages\EditCollection::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'slug'];
    }

    public static function mutateForSave(array $data): array
    {
        return StatusTransformer::transform($data);
    }

    public static function getRelations(): array
    {
        return [ItemsRelationManager::class];
    }
}
