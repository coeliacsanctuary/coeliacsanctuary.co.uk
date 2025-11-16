<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\Eateries\Schemas;

use App\Filament\Support\FindEateryLocation;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Log;
use Spatie\Geocoder\Facades\Geocoder;

class EateryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Introduction')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')->required(),

                        Toggle::make('live'),

                        Toggle::make('closed_down')->hidden(fn (string $operation): bool => $operation === 'create'),
                    ]),

                Section::make('Location')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('location')
                            ->label('Search for a location...')
                            ->dehydrated(false)
                            ->searchable()
                            ->getSearchResultsUsing(app(FindEateryLocation::class)->handle(...))
                            ->live()
                            ->afterStateUpdated(function (string $state, Set $set): void {
                                [$countryId, $countyId, $townId, $areaId] = explode('|', $state);

                                $set('country_id', $countryId);
                                $set('county_id', $countyId);
                                $set('town_id', $townId);
                                $set('area_id', $areaId);

                                $set('country', EateryCountry::query()->find($countryId)?->country);
                                $set('county', EateryCounty::query()->find($countyId)?->county);
                                $set('town', EateryTown::query()->find($townId)?->town);
                                $set('area', EateryArea::query()->find($areaId)?->area);
                            }),

                        Hidden::make('country_id'),
                        Hidden::make('county_id'),
                        Hidden::make('town_id'),
                        Hidden::make('area_id'),

                        TextInput::make('country')
                            ->dehydrated(false)
                            ->disabled()
                            ->hidden(fn (Get $get): bool => $get('location') === null)
                            ->required(),

                        TextInput::make('county')
                            ->dehydrated(false)
                            ->disabled(fn (Get $get, ?string $state): bool => $get('location') && $state)
                            ->live()
                            ->hidden(fn (Get $get): bool => $get('country') === null)
                            ->required(),

                        TextInput::make('town')
                            ->dehydrated(false)
                            ->disabled(fn (Get $get, ?string $state): bool => $get('location') && $state)
                            ->live()
                            ->hidden(fn (Get $get): bool => $get('county') === null)
                            ->required(),

                        TextInput::make('area')
                            ->dehydrated(false)
                            ->disabled(fn (Get $get, ?string $state): bool => $get('location') && $state)
                            ->live()
                            ->visible(fn (Get $get): bool => $get('county') === 'London')
                            ->required(),

                        Grid::make()
                            ->columnSpanFull()
                            ->columns(3)
                            ->schema([
                                Grid::make()
                                    ->columnSpan(2)
                                    ->schema([
                                        Textarea::make('address')
                                            ->rows(5)
                                            ->required()
                                            ->live()
                                            ->columnSpan(2)
                                            ->belowContent(
                                                Action::make('lookup')
                                                    ->button()
                                                    ->action(function (string $state, Set $set) {
                                                        $geocode = Geocoder::getCoordinatesForAddress($state);

                                                        $set('lat', $geocode['lat']);
                                                        $set('lng', $geocode['lng']);
                                                        $set('address', str_replace(', ', "\n", $geocode['formatted_address']));

                                                        $set('map', ['lat' => (float)$geocode['lat'], 'lng' => (float)$geocode['lng']]);
                                                    })
                                            ),

                                        TextInput::make('lat')
                                            ->required()
                                            ->afterStateUpdated(fn (Get $get, Set $set, $state) => $set('map', ['lat' => (float) $state, 'lng' => (float) $get('lng')]))
                                            ->reactive()
                                            ->readOnly()
                                            ->lazy(),

                                        TextInput::make('lng')
                                            ->required()
                                            ->afterStateUpdated(fn (Get $get, Set $set, $state) => $set('map', ['lng' => (float) $state, 'lat' => (float) $get('lat')]))
                                            ->reactive()
                                            ->readOnly()
                                            ->lazy(),
                                    ]),

                                Map::make('map')
                                    ->mapControls([
                                        'mapTypeControl' => false,
                                        'scaleControl' => false,
                                        'streetViewControl' => false,
                                        'rotateControl' => false,
                                        'fullscreenControl' => false,
                                        'searchBoxControl' => false,
                                        'zoomControl' => true,
                                    ])
                                    ->defaultLocation(fn (?Eatery $record): array => $record ? [$record->lat, $record->lng] : [0, 0])
                                    ->reactive()
                                    ->draggable()
                                    ->defaultZoom(15)
                                    ->afterStateUpdated(function ($state, Set $set): void {
                                        $set('lat', $state['lat']);
                                        $set('lng', $state['lng']);
                                    }),
                            ]),
                    ]),
            ]);
    }
}
