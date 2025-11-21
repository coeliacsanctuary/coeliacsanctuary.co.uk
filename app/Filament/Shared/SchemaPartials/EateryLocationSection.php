<?php

declare(strict_types=1);

namespace App\Filament\Shared\SchemaPartials;

use App\Filament\Fields\LocationLookup;
use App\Filament\Support\FindEateryLocation;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
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
use Spatie\Geocoder\Facades\Geocoder;

class EateryLocationSection
{
    public static function make(bool $isBranch = false, ?Eatery $fromChain = null): Section
    {
        return Section::make('Location')
            ->columnSpanFull()
            ->schema(function () use ($isBranch, $fromChain) {
                $schema = [];

                if ( ! $isBranch) {
                    $schema[] = Toggle::make('is_nationwide')
                        ->label('Nationwide Chain')
                        ->disabled(fn (string $operation): bool => $operation !== 'create')
                        ->dehydrated(false)
                        ->formatStateUsing(fn (?Eatery $record): bool => $record?->county_id === 1)
                        ->live()
                        ->hidden(fn (Eatery $record): bool => $record?->county_id > 1)
                        ->afterStateUpdated(function (Set $set): void {
                            $set('country_id', 1);
                            $set('county_id', 1);
                            $set('town_id', 529);
                        });
                }

                if ($isBranch) {
                    $schema[] = Select::make('wheretoeat_id')
                        ->relationship('eatery', 'name', modifyQueryUsing: fn ($query) => $query->where('county_id', 1))
                        ->default($fromChain?->id)
                        ->disabled($fromChain !== null);
                }

                return [
                    ...$schema,

                    LocationLookup::make('location')
                        ->label('Search for a location...')
                        ->hidden(function (Get $get, string $operation) use ($isBranch): bool {
                            if ($get('add_manually')) {
                                return true;
                            }

                            if ($get('unlock_location')) {
                                return false;
                            }

                            if ($operation !== 'create') {
                                return true;
                            }

                            return ! $isBranch && $get('is_nationwide');
                        })
                        ->searchable()
                        ->getSearchResultsUsing(app(FindEateryLocation::class)->handle(...))
                        ->noSearchResultsMessage('No results found, try searching for county, or add location manually')
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
                        })
                        ->dehydrated(false)
                        ->in(null)
                        ->saveRelationshipsUsing(null),

                    Toggle::make('add_manually')
                        ->dehydrated(false)
                        ->hidden(function (Get $get, string $operation) use ($isBranch): bool {
                            if ($get('location')) {
                                return true;
                            }

                            if ($get('unlock_location')) {
                                return false;
                            }

                            if ($operation !== 'create') {
                                return true;
                            }

                            return ! $isBranch && $get('is_nationwide');
                        })
                        ->live(),

                    Hidden::make('country_id'),
                    Hidden::make('county_id'),
                    Hidden::make('town_id'),
                    Hidden::make('area_id'),

                    Select::make('country')
                        ->options(EateryCountry::query()->pluck('country', 'country'))
                        ->dehydrated(false)
                        ->disabled(function (Get $get, ?string $state): bool {
                            if ($get('unlock_location')) {
                                return false;
                            }

                            if ($get('add_manually') === false) {
                                return true;
                            }

                            return $get('location') && $state;
                        })
                        ->visible(function (Get $get, string $operation) use ($isBranch): bool {
                            $isChain = ! $isBranch && $get('is_nationwide');

                            if ($operation !== 'create') {
                                return ! $isChain;
                            }

                            if ($get('add_manually')) {
                                return ! $isChain;
                            }

                            if ($get('location') !== null) {
                                return ! $isChain;
                            }

                            return false;
                        })
                        ->formatStateUsing(fn (Eatery|NationwideBranch|null $record): ?string => $record?->country?->country)
                        ->required(),

                    TextInput::make('county')
                        ->dehydrated(false)
                        ->disabled(function (Get $get, ?string $state): bool {
                            if ($get('unlock_location')) {
                                return false;
                            }

                            if ($get('add_manually') === false) {
                                return true;
                            }

                            return $get('location') && $state;
                        })
                        ->live()
                        ->visible(function (Get $get, string $operation) use ($isBranch): bool {
                            $isChain = ! $isBranch && $get('is_nationwide');

                            if ($operation !== 'create') {
                                return ! $isChain;
                            }

                            if ($get('add_manually')) {
                                return ! $isChain;
                            }

                            if ($get('country') !== null) {
                                return ! $isChain;
                            }

                            return false;
                        })
                        ->formatStateUsing(fn (Eatery|NationwideBranch|null $record): ?string => $record?->county?->county)
                        ->required(),

                    TextInput::make('town')
                        ->dehydrated(false)
                        ->disabled(function (Get $get, ?string $state): bool {
                            if ($get('unlock_location')) {
                                return false;
                            }

                            if ($get('add_manually') === false) {
                                return true;
                            }

                            return $get('location') && $state;
                        })
                        ->live()
                        ->visible(function (Get $get, string $operation) use ($isBranch): bool {
                            $isChain = ! $isBranch && $get('is_nationwide');

                            if ($operation !== 'create') {
                                return ! $isChain;
                            }

                            if ($get('add_manually')) {
                                return ! $isChain;
                            }

                            if ($get('county') !== null) {
                                return ! $isChain;
                            }

                            return false;
                        })
                        ->formatStateUsing(fn (Eatery|NationwideBranch|null $record): ?string => $record?->town?->town)
                        ->required(),

                    TextInput::make('area')
                        ->dehydrated(false)
                        ->disabled(function (Get $get, ?string $state): bool {
                            if ($get('unlock_location')) {
                                return false;
                            }

                            if ($get('add_manually') === false) {
                                return true;
                            }

                            return $get('location') && $state;
                        })
                        ->live()
                        ->visible(function (Get $get, string $operation) use ($isBranch): bool {
                            if ($get('county') !== 'London') {
                                return false;
                            }

                            $isChain = ! $isBranch && $get('is_nationwide');

                            if ($operation !== 'create') {
                                return ! $isChain;
                            }

                            if ($get('add_manually')) {
                                return ! $isChain;
                            }

                            if ($get('country') !== null) {
                                return ! $isChain;
                            }

                            return false;
                        })
                        ->formatStateUsing(fn (Eatery|NationwideBranch|null $record): ?string => $record?->area?->area)
                        ->required(),

                    Action::make('reset_location')
                        ->button()
                        ->color('danger')
                        ->visible(fn (Get $get): bool => $get('location') && ($get('country') || $get('county') || $get('town')))
                        ->action(function (Set $set): void {
                            $set('country_id', null);
                            $set('county_id', null);
                            $set('town_id', null);
                            $set('area_id', null);

                            $set('country', null);
                            $set('county', null);
                            $set('town', null);
                            $set('area', null);

                            $set('location', null);
                        }),

                    Hidden::make('unlock_location')->default(false),

                    Action::make('unlock_location_action')
                        ->label('Unlock Location Fields')
                        ->button()
                        ->visible(function (Get $get, string $operation) use ($isBranch): bool {
                            $isChain = ! $isBranch && $get('is_nationwide');

                            if ($isChain) {
                                return false;
                            }

                            if ($get('unlock_location')) {
                                return false;
                            }

                            if ($operation !== 'create') {
                                return true;
                            }

                            return $get('location') && ($get('country') || $get('county') || $get('town'));
                        })
                        ->action(function (Set $set): void {
                            $set('unlock_location', true);
                        }),

                    Grid::make()
                        ->columnSpanFull()
                        ->hidden(fn (Get $get): bool => ( ! $isBranch && $get('is_nationwide')))
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
                                                ->action(function (string $state, Set $set): void {
                                                    $geocode = Geocoder::getCoordinatesForAddress($state);

                                                    $set('lat', $geocode['lat']);
                                                    $set('lng', $geocode['lng']);
                                                    $set('address', str_replace(', ', "\n", $geocode['formatted_address']));

                                                    $set('map', ['lat' => (float) $geocode['lat'], 'lng' => (float) $geocode['lng']]);
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
                                ->defaultLocation(fn (Eatery|NationwideBranch|null $record): array => $record ? [$record->lat, $record->lng] : [0, 0])
                                ->reactive()
                                ->draggable()
                                ->dehydrated(false)
                                ->defaultZoom(15)
                                ->afterStateUpdated(function ($state, Set $set): void {
                                    $set('lat', $state['lat']);
                                    $set('lng', $state['lng']);
                                }),
                        ]),
                ];

                return $schema;
            });
    }
}
