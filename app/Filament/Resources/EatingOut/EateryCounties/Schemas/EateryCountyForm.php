<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryCounties\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

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
                        Select::make('country_id')
                            ->relationship('country', 'country')
                            ->live()
                            ->required(),

                        TextInput::make('county')
                            ->required()
                            ->label('Name')
                            ->live()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', Str::slug($state))),

                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('country_id', $get('country_id'))),

                        SpatieMediaLibraryFileUpload::make('primary')
                            ->label('Image')
                            ->required()
                            ->collection('primary')
                            ->imagePreviewHeight('176px'),
                    ]),
            ]);
    }
}
