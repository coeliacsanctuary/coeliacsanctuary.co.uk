<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryCounties\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class EateryCountyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(1)
                    ->schema([
                        TextInput::make('county')
                            ->label('Name')
                            ->required()
                            ->maxLength(200)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Get $get, Set $set, ?string $old, ?string $state, string $operation): void {
                                if ($operation !== 'create' || ($get('slug') ?? '') !== Str::slug((string) $old)) {
                                    return;
                                }

                                $set('slug', Str::slug((string) $state));
                            }),

                        TextInput::make('slug')
                            ->visibleOn('create')
                            ->required()
                            ->maxLength(200)
                            ->unique(),

                        Select::make('country_id')
                            ->label('Country')
                            ->relationship('country', 'country')
                            ->required(),

                        TextInput::make('latlng')
                            ->label('Lat / Lng')
                            ->nullable(),

                        SpatieMediaLibraryFileUpload::make('primary')
                            ->label('Image')
                            ->collection('primary')
                            ->image()
                            ->imagePreviewHeight('176px'),
                    ]),
            ]);
    }
}
