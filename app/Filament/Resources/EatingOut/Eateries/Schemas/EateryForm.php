<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\Eateries\Schemas;

use App\Enums\EatingOut\EateryType;
use App\Filament\Schemas\Components\EateryIntroductionSection;
use App\Filament\Schemas\Components\EateryLocationSection;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class EateryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                EateryIntroductionSection::make([
                    Toggle::make('closed_down')->hidden(fn (string $operation): bool => $operation === 'create'),
                ]),

                EateryLocationSection::make(),

                Section::make('Contact Details')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('phone')->label('Phone Number'),
                        TextInput::make('website'),
                        TextInput::make('gf_menu_link')->label('GF Menu Link'),
                    ]),

                Section::make('Details')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('type_id')
                            ->relationship('type', 'name', fn ($query) => $query->reorder('id'))
                            ->columnSpanFull()
                            ->live()
                            ->required(),

                        Grid::make()
                            ->visible(fn (Get $get): bool => $get('type_id') !== null)
                            ->columnSpanFull()
                            ->schema([
                                Select::make('venue_type_id')
                                    ->hidden(fn (Get $get) => $get('type_id') === EateryType::HOTEL->value)
                                    ->relationship('venueType', 'venue_type', fn (Builder $query, Get $get) => $query->where('type_id', $get('type_id'))->reorder('venue_type'))
                                    ->required(),

                                Select::make('cuisine_id')
                                    ->hidden(fn (Get $get) => $get('type_id') === EateryType::HOTEL->value)
                                    ->relationship('cuisine', 'cuisine', fn (Builder $query, Get $get) => $query->reorder('cuisine'))
                                    ->required(),

                                Textarea::make('info')
                                    ->columnSpanFull()
                                    ->visible(fn (Get $get): bool => $get('type_id') !== EateryType::ATTRACTION->value)
                                    ->rows(5)
                                    ->required(),

                                Repeater::make('restaurants')
                                    ->columnSpanFull()
                                    ->relationship()
                                    ->visible(fn (Get $get): bool => $get('type_id') === EateryType::ATTRACTION->value)
                                    ->schema([
                                        TextInput::make('restaurant_name')->dehydrateStateUsing(fn (?string $state): string => $state ?: ''),

                                        Textarea::make('info')->rows(3)->required(),
                                    ]),
                            ]),
                    ]),

                Section::make('Features')
                    ->columnSpanFull()
                    ->columns(1)
                    ->schema([
                        CheckboxList::make('features')
                            ->hiddenLabel()
                            ->relationship(titleAttribute: 'feature')
                            ->bulkToggleable()
                            ->columns(4),
                    ]),

                EateryOpeningTimes::make(),
            ]);
    }
}
