<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Recipes;

use App\Filament\Resources\MainSite\Recipes\Schemas\RecipeForm;
use App\Filament\Resources\RecipeResource\Pages;
use App\Filament\Resources\MainSite\Recipes\RelationManagers\CommentsRelationManager;
use App\Filament\Resources\MainSite\Recipes\Tables\RecipesTable;
use App\Filament\Transformers\StatusTransformer;
use App\Models\Recipes\Recipe;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class RecipeResource extends Resource
{
    protected static ?string $model = Recipe::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 2;

    protected static string|UnitEnum|null $navigationGroup = 'Main Site';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->reorder('id', 'desc');
    }

    public static function form(Schema $schema): Schema
    {
        return RecipeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RecipesTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Resources\MainSite\Recipes\Pages\ListRecipes::route('/'),
            'create' => \App\Filament\Resources\MainSite\Recipes\Pages\CreateRecipe::route('/create'),
            'edit' => \App\Filament\Resources\MainSite\Recipes\Pages\EditRecipe::route('/{record}/edit'),
        ];
    }

    public static function mutateForSave(array $data): array
    {
        return StatusTransformer::transform($data);
    }

    public static function getRelations(): array
    {
        return [CommentsRelationManager::class];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'slug'];
    }
}
