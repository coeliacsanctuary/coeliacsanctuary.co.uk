<?php

declare(strict_types=1);

namespace App\Filament\Schemas\Components;

use App\Filament\Forms\Components\LocationLookup;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryArea;
use App\Models\EatingOut\EateryCountry;
use App\Models\EatingOut\EateryCounty;
use App\Models\EatingOut\EateryTown;
use App\Models\EatingOut\NationwideBranch;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;
use Spatie\Geocoder\Facades\Geocoder;

class EateryLocationSection
{
    public static function make(bool $isBranch = false, ?Eatery $fromChain = null): Section
    {
        return Section::make('Location')
            ->columnSpanFull()
            ->schema(function () use ($isBranch, $fromChain): array {
                $schema = [];

                $isChain = fn (Get $get): bool => ! $isBranch && $get('is_nationwide');

                if ( ! $isBranch) {
                    $schema[] = Toggle::make('is_nationwide')
                        ->label('Nationwide Chain')
                        ->disabled(fn (string $operation): bool => $operation !== 'create')
                        ->dehydrated(false)
                        ->formatStateUsing(fn (?Eatery $record, ?bool $state): bool => $record instanceof Eatery ? $record->county_id === 1 : (bool) $state)
                        ->live()
                        ->hidden(fn (?Eatery $record): bool => $record?->county_id > 1)
                        ->afterStateUpdated(function (bool $state, Set $set): void {
                            $set('country_id', $state ? 1 : null);
                            $set('county_id', $state ? 1 : null);
                            $set('town_id', $state ? 529 : null);
                            $set('area_id', null);
                        });
                }

                if ($isBranch) {
                    $schema[] = Select::make('wheretoeat_id')
                        ->relationship('eatery', 'name', modifyQueryUsing: fn ($query) => $query->where('county_id', 1))
                        ->default($fromChain?->id)
                        ->disabled($fromChain instanceof Eatery);
                }

                return [
                    ...$schema,

                    LocationLookup::make('location')->hidden($isChain),

                    Select::make('country_id')
                        ->label('Country')
                        ->options(fn (): array => EateryCountry::query()->pluck('country', 'id')->all())
                        ->searchable()
                        ->live()
                        ->required()
                        ->hidden($isChain)
                        ->dehydratedWhenHidden()
                        ->afterStateUpdated(function (Set $set): void {
                            $set('county_id', null);
                            $set('town_id', null);
                            $set('area_id', null);
                        }),

                    Select::make('county_id')
                        ->label('County')
                        ->options(fn (Get $get): array => EateryCounty::query()
                            ->withoutGlobalScopes()
                            ->where('country_id', $get('country_id'))
                            ->orderBy('county')
                            ->pluck('county', 'id')
                            ->all())
                        ->searchable()
                        ->live()
                        ->required()
                        ->hidden($isChain)
                        ->dehydratedWhenHidden()
                        ->afterStateUpdated(function (Set $set): void {
                            $set('town_id', null);
                            $set('area_id', null);
                        })
                        ->createOptionModalHeading('Add County')
                        ->createOptionForm(self::createPlaceForm('county', EateryCounty::class))
                        ->createOptionAction(fn (Action $action, Get $get): Action => $action->visible(filled($get('country_id'))))
                        ->createOptionUsing(fn (array $data, Get $get): int => EateryCounty::query()->create([
                            ...$data,
                            'country_id' => $get('country_id'),
                            'legacy' => $data['slug'],
                        ])->id),

                    Select::make('town_id')
                        ->label(fn (Get $get): string => self::isLondon($get('county_id')) ? 'Borough' : 'Town')
                        ->options(fn (Get $get): array => EateryTown::query()
                            ->withoutGlobalScopes()
                            ->where('county_id', $get('county_id'))
                            ->orderBy('town')
                            ->pluck('town', 'id')
                            ->all())
                        ->searchable()
                        ->live()
                        ->required()
                        ->hidden($isChain)
                        ->dehydratedWhenHidden()
                        ->afterStateUpdated(fn (Set $set): mixed => $set('area_id', null))
                        ->createOptionModalHeading(fn (Get $get): string => 'Add Town to ' . EateryCounty::query()->withoutGlobalScopes()->find($get('county_id'))?->county)
                        ->createOptionForm(fn (Get $get): array => self::createPlaceForm('town', EateryTown::class, 'county_id', (int) $get('county_id')))
                        ->createOptionAction(fn (Action $action, Get $get): Action => $action->visible(filled($get('county_id'))))
                        ->createOptionUsing(fn (array $data, Get $get): int => EateryTown::query()->create([
                            ...$data,
                            'county_id' => $get('county_id'),
                            'legacy' => $data['slug'],
                        ])->id),

                    Select::make('area_id')
                        ->label('Area')
                        ->options(fn (Get $get): array => EateryArea::query()
                            ->withoutGlobalScopes()
                            ->where('town_id', $get('town_id'))
                            ->orderBy('area')
                            ->pluck('area', 'id')
                            ->all())
                        ->searchable()
                        ->required(fn (Get $get): bool => self::isLondon($get('county_id')))
                        ->hidden(fn (Get $get): bool => $isChain($get) || ! self::isLondon($get('county_id')))
                        ->dehydratedWhenHidden()
                        ->createOptionModalHeading(fn (Get $get): string => 'Add Area to ' . EateryTown::query()->withoutGlobalScopes()->find($get('town_id'))?->town)
                        ->createOptionForm(fn (Get $get): array => self::createPlaceForm('area', EateryArea::class, 'town_id', (int) $get('town_id')))
                        ->createOptionAction(fn (Action $action, Get $get): Action => $action->visible(filled($get('town_id'))))
                        ->createOptionUsing(fn (array $data, Get $get): int => EateryArea::query()->create([
                            ...$data,
                            'town_id' => $get('town_id'),
                        ])->id),

                    Grid::make()
                        ->columnSpanFull()
                        ->hidden($isChain)
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
                                        ->afterStateUpdated(fn (Get $get, Set $set, $state): mixed => $set('map', ['lat' => (float) $state, 'lng' => (float) $get('lng')]))
                                        ->reactive()
                                        ->readOnly()
                                        ->lazy(),

                                    TextInput::make('lng')
                                        ->required()
                                        ->afterStateUpdated(fn (Get $get, Set $set, $state): mixed => $set('map', ['lng' => (float) $state, 'lat' => (float) $get('lat')]))
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
                                ->afterStateUpdated(function (array $state, Set $set): void {
                                    $set('lat', $state['lat']);
                                    $set('lng', $state['lng']);
                                }),
                        ]),
                ];
            });
    }

    protected static function isLondon(mixed $countyId): bool
    {
        if (blank($countyId)) {
            return false;
        }

        return EateryCounty::query()->withoutGlobalScopes()->whereKey($countyId)->where('slug', 'london')->exists();
    }

    /**
     * @param  class-string<Model>  $model
     * @return array<int, TextInput>
     */
    protected static function createPlaceForm(string $nameColumn, string $model, ?string $parentColumn = null, ?int $parentId = null): array
    {
        return [
            TextInput::make($nameColumn)
                ->label('Name')
                ->required()
                ->maxLength(200)
                ->live(onBlur: true)
                ->afterStateUpdated(fn (?string $state, Set $set): mixed => $set('slug', Str::slug((string) $state))),

            TextInput::make('slug')
                ->required()
                ->maxLength(200)
                ->unique(
                    table: $model,
                    column: 'slug',
                    ignoreRecord: false,
                    modifyRuleUsing: fn (Unique $rule): Unique => $parentColumn ? $rule->where($parentColumn, $parentId) : $rule,
                ),

            TextInput::make('latlng')
                ->label('Lat / Lng')
                ->nullable(),
        ];
    }
}
