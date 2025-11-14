<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Recipes\Schemas;

use App\Filament\Shared\SchemaPartials\ImagesSection;
use App\Filament\Shared\SchemaPartials\MetasSection;
use App\Filament\Shared\SchemaPartials\VisibilitySection;
use App\Models\Recipes\Recipe;
use App\Models\Recipes\RecipeAllergen;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class RecipeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make()
                ->columns(5)
                ->columnSpanFull()
                ->schema([
                    Section::make('Introduction')
                        ->columnSpan(4)
                        ->schema([
                            TextInput::make('title')
                                ->required()
                                ->maxLength(200)
                                ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state): void {
                                    if (($get('slug') ?? '') !== Str::slug((string) $old)) {
                                        return;
                                    }

                                    $set('slug', Str::slug((string) $state));
                                })
                                ->live(),

                            TextInput::make('slug')
                                ->required()
                                ->maxLength(200)
                                ->regex('/^[a-z0-9-]+$/'),

                            TextInput::make('search_tags')->required(),

                            TextInput::make('author')->default('Alison Peters')->required(),
                        ]),

                    VisibilitySection::make()->columnSpan(1),
                ]),

            MetasSection::make()->columnSpanFull(),

            ImagesSection::make(squareImage: true)->columnSpanFull(),

            Section::make('Recipe')
                ->columnSpanFull()
                ->schema([
                    Grid::make()
                        ->columns(5)
                        ->columnSpanFull()
                        ->schema([
                            Textarea::make('ingredients')
                                ->autosize()
                                ->columnSpan(4)
                                ->required(),

                            Grid::make()->columns(1)->schema([
                                TextInput::make('prep_time')
                                    ->required()
                                    ->columnSpan(1)
                                    ->maxLength(50),

                                TextInput::make('cook_time')
                                    ->required()
                                    ->columnSpan(1)
                                    ->maxLength(50),
                            ]),

                            Textarea::make('method')
                                ->autosize()
                                ->columnSpan(4)
                                ->required(),

                            Grid::make()->columns(1)->schema([
                                TextInput::make('serving_size')
                                    ->required()
                                    ->columnSpan(1)
                                    ->maxLength(50),

                                TextInput::make('per')->label('Nutrition Per...')
                                    ->required()
                                    ->columnSpan(1)
                                    ->maxLength(50),
                            ]),
                        ]),
                ]),

            Section::make('Nutritional Information')
                ->columns(6)
                ->columnSpanFull()
                ->relationship('nutrition')
                ->schema([
                    TextInput::make('calories')
                        ->numeric()
                        ->required()
                        ->label('Calories')
                        ->inlineLabel(false),

                    TextInput::make('carbs')
                        ->numeric()
                        ->required()
                        ->label('Carbs')
                        ->inlineLabel(false),

                    TextInput::make('fat')
                        ->numeric()
                        ->required()
                        ->label('Fat')
                        ->inlineLabel(false),

                    TextInput::make('protein')
                        ->numeric()
                        ->required()
                        ->label('Protein')
                        ->inlineLabel(false),

                    TextInput::make('fibre')
                        ->numeric()
                        ->required()
                        ->label('Fibre')
                        ->inlineLabel(false),

                    TextInput::make('sugar')
                        ->numeric()
                        ->required()
                        ->label('Sugar')
                        ->inlineLabel(false),
                ]),

            Section::make('Allergens')
                ->columnSpanFull()
                ->schema([
                    CheckboxList::make('allergens')
                        ->hiddenLabel()
                        ->relationship('allergens', 'allergen')
                        ->label('Allergens')
                        ->columns(5)
                        ->columnSpanFull()
                        ->formatStateUsing(function (array $state, ?Recipe $record) {
                            if(!$record) {
                                return [];
                            }

                            $all = RecipeAllergen::query()->pluck('id')->toArray();
                            $attached = $record->allergens()->pluck('id')->toArray();

                            return collect($all)
                                ->diff($attached)
                                ->values()
                                ->toArray();
                        })
                        ->mutateDehydratedStateUsing(function (?array $state) {
                            $all = RecipeAllergen::pluck('id')->toArray();

                            $checked = collect($state ?? []);       // boxes user left checked
                            $unchecked = collect($all)->diff($checked); // boxes left unchecked

                            return $unchecked->values()->toArray();
                        })
                        ->saveRelationshipsUsing(function (Recipe $record, array $state): void {
                            $record->allergens()->syncWithoutDetaching($state);
                        }),
                ]),

            Section::make('Meals')
                ->columnSpanFull()
                ->schema([
                    CheckboxList::make('meals')
                        ->hiddenLabel()
                        ->relationship('meals', 'meal')
                        ->label('Meals')
                        ->columns(5)
                        ->columnSpanFull()
                        ->bulkToggleable(),
                ]),

            Section::make('Features')
                ->columnSpanFull()
                ->schema([
                    CheckboxList::make('features')
                        ->hiddenLabel()
                        ->relationship('features', 'feature')
                        ->label('Features')
                        ->columns(5)
                        ->columnSpanFull()
                        ->bulkToggleable(),
                ]),
        ]);
    }
}
