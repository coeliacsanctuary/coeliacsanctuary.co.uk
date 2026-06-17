<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryTowns\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

class EateryTownForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(1)
                    ->schema([
                        Select::make('county_id')
                            ->relationship('county', 'county')
                            ->live()
                            ->required(),

                        TextInput::make('town')
                            ->required()
                            ->label('Name')
                            ->live()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),

                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('county_id', $get('county_id'))),

                        TextInput::make('lat')
                            ->label('Latitude'),

                        TextInput::make('lng')
                            ->label('Longitude'),

                        SpatieMediaLibraryFileUpload::make('primary')
                            ->label('Image')
                            ->required()
                            ->collection('primary')
                            ->imagePreviewHeight('176px'),
                    ]),
            ]);
    }
}
