<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Collections\Schemas;

use App\Enums\Collections\CollectionDisplayType;
use App\Filament\Forms\Components\Body;
use App\Filament\Forms\Components\CollectionItemSelect;
use App\Filament\Schemas\Components\ImagesSection;
use App\Filament\Schemas\Components\MetasSection;
use App\Filament\Schemas\Components\VisibilitySection;
use App\Models\Blogs\Blog;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Models\Recipes\Recipe;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CollectionForm
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

                            TextInput::make('slug')
                                ->required()
                                ->maxLength(200)
                                ->regex('/^[a-z0-9-]+$/')
                                ->disabledOn('edit')
                                ->unique(),

                            Textarea::make('long_description')
                                ->label('Description')
                                ->rows(4)
                                ->required()
                                ->columnSpanFull(),
                        ]),

                    Grid::make()->columns(1)->schema([
                        VisibilitySection::make()->columnSpan(1),

                        Section::make('Display on Homepage')
                            ->visible(fn (Get $get): bool => $get('status') === 'live')
                            ->schema([
                                Toggle::make('display_on_homepage')->live(),

                                DateTimePicker::make('remove_from_homepage')
                                    ->label('Hide from homepage on')
                                    ->visible(fn (Get $get): bool => $get('display_on_homepage')),

                                Select::make('items_to_display')
                                    ->label('Number of items to show on homepage')
                                    ->visible(fn (Get $get): bool => $get('display_on_homepage'))
                                    ->default(3)
                                    ->options([
                                        '1' => '1 item',
                                        '2' => '2 items',
                                        '3' => '3 items',
                                        '4' => '4 items',
                                        '6' => '6 items',
                                        '8' => '8 items',
                                    ]),
                            ]),
                    ]),
                ]),

            MetasSection::make(metaTags: 'meta_keywords')->columnSpanFull(),

            ImagesSection::make(headerImageAltText: true)->columnSpanFull(),

            Section::make('Content')
                ->columnSpanFull()
                ->schema([
                    Body::make('body')
                        ->rows(5)
                        ->required()
                        ->autosize(),
                ]),

            Section::make('Layout')
                ->columnSpanFull()
                ->schema([
                    Select::make('display_type')
                        ->label('Display Type')
                        ->required()
                        ->default(CollectionDisplayType::GRID->value)
                        ->options(collect(CollectionDisplayType::cases())
                            ->mapWithKeys(fn (CollectionDisplayType $type): array => [$type->value => $type->name()])
                            ->all())
                        ->helperText('Grid lays the items out as cards, best for recipe and blog collections. List stacks them vertically as rows under each group heading, best for eateries broken down by county.'),
                ]),

            Section::make('Groups')
                ->columnSpanFull()
                ->schema([
                    Repeater::make('groups')
                        ->relationship()
                        ->hiddenLabel()
                        ->columnSpanFull()
                        ->orderColumn('position')
                        ->collapsible()
                        ->collapsed(fn (string $operation): bool => $operation === 'edit')
                        ->itemLabel(fn (array $state): ?string => $state['title'] ?? null)
                        ->schema([
                            TextInput::make('title')
                                ->maxLength(255)
                                ->columnSpanFull(),

                            Body::make('body')
                                ->rows(3)
                                ->toolbar(false)
                                ->columnSpanFull(),

                            Repeater::make('items')
                                ->relationship()
                                ->columnSpanFull()
                                ->orderColumn('position')
                                ->columns(2)
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

                                    CollectionItemSelect::make('item_id')
                                        ->label('Item')
                                        ->required()
                                        ->disabled(fn (Get $get) => ! $get('item_type')),

                                    TextInput::make('item_title')
                                        ->label('Title override')
                                        ->helperText("Leave blank to use the item's own title")
                                        ->maxLength(255),

                                    Textarea::make('item_description')
                                        ->label('Description override')
                                        ->helperText("Leave blank to use the item's own description")
                                        ->columnSpanFull(),
                                ]),
                        ]),
                ]),
        ]);
    }
}
