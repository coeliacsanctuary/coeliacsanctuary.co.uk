<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Collections\RelationManagers;

use App\Models\Blogs\Blog;
use App\Models\Collections\CollectionItem;
use App\Models\Recipes\Recipe;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('item_type')
                    ->options([
                        Blog::class => 'Blog',
                        Recipe::class => 'Recipe',
                    ])
                    ->live()
                    ->required(),

                Select::make('item_id')
                    ->label('Item')
                    ->required()
                    ->disabled(fn (Get $get) => ! $get('item_type'))
                    ->searchable()
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set): void {
                        if (filled($get('description'))) {
                            return;
                        }

                        $item = $get('item_type')::query()->findOrFail($get('item_id'));

                        $set('description', $item->meta_description);
                    })
                    ->options(function (Get $get) {
                        if ( ! $get('item_type')) {
                            return [];
                        }

                        return $get('item_type')::query()->latest()->pluck('title', 'id');
                    }),

                Textarea::make('description')
                    ->required()
                    ->columnSpanFull(),

                Hidden::make('position')->default(fn () => $this->getOwnerRecord()->items()->max('position') + 1),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('item.title')
            ->modifyQueryUsing(fn ($query) => $query->reorder('position'))
            ->reorderable('position')
            ->paginated(false)
            ->columns([
                TextColumn::make('item.title')
                    ->searchable()
                    ->prefix(fn (CollectionItem $item): string => class_basename($item->item_type) . ' - '),
                TextColumn::make('position')
                    ->numeric()
                    ->sortable(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
