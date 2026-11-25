<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryTowns\Schemas;

use App\Filament\Resources\EatingOut\EateryBoroughs\EateryBoroughResource;
use App\Models\EatingOut\EateryCounty;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

class EateryTownForm
{
    public static function configure(Schema $schema, bool $isBorough = false): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(1)
                    ->schema([
                        TextInput::make('town')
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
                            ->unique(modifyRuleUsing: fn (Unique $rule, Get $get): Unique => $rule->where(
                                'county_id',
                                $isBorough ? EateryBoroughResource::londonId() : $get('county_id'),
                            )),

                        ...$isBorough ? [] : [self::countySelect()],

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

    /** @return array<int, string> */
    public static function countyOptions(): array
    {
        return EateryCounty::query()
            ->withoutGlobalScopes()
            ->with(['country'])
            ->whereNotIn('slug', ['london', 'nationwide'])
            ->get()
            ->mapWithKeys(fn (EateryCounty $county): array => [$county->id => "{$county->country?->country} - {$county->county}"])
            ->sort()
            ->all();
    }

    protected static function countySelect(): Select
    {
        return Select::make('county_id')
            ->label('County')
            ->options(self::countyOptions(...))
            ->searchable()
            ->required();
    }
}
