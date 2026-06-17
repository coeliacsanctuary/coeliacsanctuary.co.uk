<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Collections\RelationManagers;

use App\Filament\Forms\Components\Body;
use App\Models\Blogs\Blog;
use App\Models\Collections\CollectionGroup;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Models\Recipes\Recipe;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class GroupsRelationManager extends RelationManager
{
    protected static string $relationship = 'groups';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->maxLength(255),

                Body::make('body')
                    ->rows(3)
                    ->toolbar(false)
                    ->columnSpanFull(),

                Repeater::make('items')
                    ->relationship()
                    ->orderColumn('position')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('item_type')
                            ->label('Type')
                            ->options([
                                Blog::class => 'Blog',
                                Recipe::class => 'Recipe',
                                Eatery::class => 'Eatery',
                                NationwideBranch::class => 'Nationwide Branch',
                            ])
                            ->required()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('item_id', null)),

                        Select::make('item_id')
                            ->label('Item')
                            ->required()
                            ->disabled(fn (Get $get) => ! $get('item_type'))
                            ->searchable()
                            ->getSearchResultsUsing(fn (Get $get, string $search): array => $get('item_type')::query()
                                ->where(static::labelColumn($get('item_type')), 'like', "%{$search}%")
                                ->limit(50)
                                ->pluck(static::labelColumn($get('item_type')), 'id')
                                ->all())
                            ->getOptionLabelUsing(fn (Get $get, $value): ?string => $get('item_type')::query()
                                ->find($value)
                                ?->{static::labelColumn($get('item_type'))}),

                        TextInput::make('item_title')
                            ->label('Title override')
                            ->helperText("Leave blank to use the item's own title")
                            ->maxLength(255),

                        Textarea::make('item_description')
                            ->label('Description override')
                            ->helperText("Leave blank to use the item's own description")
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->modifyQueryUsing(fn ($query) => $query->reorder('position'))
            ->reorderable('position')
            ->paginated(false)
            ->columns([
                TextColumn::make('title')
                    ->placeholder('Untitled group'),

                TextColumn::make('body')
                    ->limit(50)
                    ->placeholder('—'),

                TextColumn::make('items_count')
                    ->label('Items')
                    ->state(fn (CollectionGroup $record): int => $record->items()->count()),
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

    /** @param class-string<Model> $itemType */
    protected static function labelColumn(string $itemType): string
    {
        return match ($itemType) {
            Eatery::class, NationwideBranch::class => 'name',
            default => 'title',
        };
    }
}
