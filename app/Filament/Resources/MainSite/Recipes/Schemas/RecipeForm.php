<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Recipes\Schemas;

use App\Filament\Forms\Components\Body;
use App\Filament\Forms\Components\RelatedRecipesSearch;
use App\Filament\Schemas\Components\FaqsSection;
use App\Filament\Schemas\Components\ImagesSection;
use App\Filament\Schemas\Components\MetasSection;
use App\Filament\Schemas\Components\VisibilitySection;
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
                                ->live(onBlur: true),

                            TextInput::make('short_title')
                                ->maxLength(100)
                                ->helperText('Optional, used with FAQs')
                                ->nullable(),

                            TextInput::make('slug')
                                ->required()
                                ->maxLength(200)
                                ->regex('/^[a-z0-9-]+$/')
                                ->disabledOn('edit')
                                ->unique(),

                            TextInput::make('search_tags')->required(),

                            TextInput::make('author')
                                ->required()
                                ->maxLength(255),

                            Textarea::make('description')
                                ->required()
                                ->rows(4)
                                ->columnSpanFull(),
                        ]),

                    VisibilitySection::make()->columnSpan(1),
                ]),

            MetasSection::make()->columnSpanFull(),

            ImagesSection::make(squareImage: true, headerImageAltText: true)->columnSpanFull(),

            Section::make('Recipe')
                ->columnSpanFull()
                ->schema([
                    Body::make('body')
                        ->nullable()
                        ->validHtml()
                        ->images()
                        ->columnSpanFull(),

                    Grid::make()
                        ->columns(5)
                        ->columnSpanFull()
                        ->schema([
                            Body::make('ingredients')
                                ->autosize()
                                ->toolbar(false)
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

                            Body::make('method')
                                ->autosize()
                                ->toolbar(false)
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

                    TextInput::make('df_to_not_df')
                        ->label('DF to not DF')
                        ->nullable()
                        ->maxLength(255)
                        ->columnSpanFull()
                        ->visible(fn (?Recipe $record): bool => filled($record?->df_to_not_df)),
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
                ->description('Tick the allergens that apply to this recipe.')
                ->columnSpanFull()
                ->schema([
                    CheckboxList::make('allergens')
                        ->hiddenLabel()
                        ->relationship('allergens', 'allergen')
                        ->label('Allergens')
                        ->columns(5)
                        ->columnSpanFull()
                        ->formatStateUsing(function (array $state, ?Recipe $record) {
                            if ( ! $record) {
                                return [];
                            }

                            $all = RecipeAllergen::query()->pluck('id')->toArray();
                            $attached = $record->allergens()->pluck('id')->toArray();

                            return collect($all)
                                ->diff($attached)
                                ->values()
                                ->toArray();
                        })
                        ->saveRelationshipsUsing(function (Recipe $record, array $state): void {
                            $record->allergens()->sync(
                                RecipeAllergen::query()
                                    ->pluck('id')
                                    ->diff($state)
                                    ->values()
                                    ->all()
                            );
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
                        ->columnSpanFull(),
                ]),

            Section::make('Features')
                ->columnSpanFull()
                ->schema([
                    CheckboxList::make('features')
                        ->hiddenLabel()
                        ->relationship('features', 'feature')
                        ->label('Features')
                        ->columns(5)
                        ->columnSpanFull(),
                ]),

            FaqsSection::make()->columnSpanFull(),

            Section::make('Related Recipes')
                ->columnSpanFull()
                ->collapsible()
                ->collapsed(fn (string $operation): bool => $operation !== 'create')
                ->schema([
                    RelatedRecipesSearch::make('relatedRecipes')
                        ->hiddenLabel()
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
