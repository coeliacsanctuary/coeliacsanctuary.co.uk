<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Forms\Recipes\RecipeForm;
use App\Filament\Resources\RecipeResource\Pages;
use App\Filament\Tables\Recipes\RecipeTable;
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

    protected static ?string $slug = 'recipes';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 1;

    protected static string|UnitEnum|null $navigationGroup = 'Main Site';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->reorder('id', 'desc');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components(RecipeForm::make());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(RecipeTable::make())
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('view')
                    ->visible(fn (Recipe $record) => $record->live)
                    ->icon(Heroicon::Eye)
                    ->label('View')
                    ->url(fn (Recipe $record) => $record->absolute_link)
                    ->openUrlInNewTab(),

                EditAction::make(),
            ]);

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
        $data['live'] = match ($data['status']) {
            'Live' => true,
            default => false,
        };

        unset($data['status']);

        return $data;
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['title', 'slug'];
    }
}
