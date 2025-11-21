<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Recipes;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\MainSite\Recipes\RelationManagers\CommentsRelationManager;
use App\Filament\Resources\MainSite\Recipes\Schemas\RecipeForm;
use App\Filament\Resources\MainSite\Recipes\Tables\RecipesTable;
use App\Filament\Transformers\StatusTransformer;
use App\Models\Recipes\Recipe;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RecipeResource extends BaseResource
{
    protected static ?string $model = Recipe::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCake;

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
            'index' => Pages\ListRecipes::route('/'),
            'create' => Pages\CreateRecipe::route('/create'),
            'edit' => Pages\EditRecipe::route('/{record}/edit'),
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
