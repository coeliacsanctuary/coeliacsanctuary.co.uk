<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Collections\Schemas;

use App\Filament\Shared\SchemaPartials\ImagesSection;
use App\Filament\Shared\SchemaPartials\MetasSection;
use App\Filament\Shared\SchemaPartials\VisibilitySection;
use Filament\Forms\Components\DateTimePicker;
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
                                ->live(),

                            TextInput::make('slug')
                                ->required()
                                ->maxLength(200)
                                ->regex('/^[a-z0-9-]+$/'),
                        ]),

                    Grid::make()->columns(1)->schema([
                        VisibilitySection::make()->columnSpan(1),

                        Section::make()
                            ->schema([
                                Toggle::make('display_on_homepage')->live(),

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
                                    ])
                                    ->live(),

                                DateTimePicker::make('remove_from_homepage')
                                    ->label('Hide from homepage on')
                                    ->visible(fn (Get $get): bool => $get('display_on_homepage'))
                                    ->live(),
                            ]),
                    ]),
                ]),

            MetasSection::make()->columnSpanFull(),

            ImagesSection::make()->columnSpanFull(),

            Section::make('Content')
                ->columnSpanFull()
                ->schema([
                    Textarea::make('body')
                        ->rows(5)
                        ->required()
                        ->autosize(),
                ]),
        ]);
    }
}
