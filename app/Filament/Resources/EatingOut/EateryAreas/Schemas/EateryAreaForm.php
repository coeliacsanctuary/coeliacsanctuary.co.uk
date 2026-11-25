<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryAreas\Schemas;

use App\Filament\Resources\EatingOut\EateryBoroughs\EateryBoroughResource;
use App\Models\EatingOut\EateryTown;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Unique;

class EateryAreaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(1)
                    ->schema([
                        TextInput::make('area')
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
                            ->unique(modifyRuleUsing: fn (Unique $rule, Get $get): Unique => $rule->where('town_id', $get('town_id'))),

                        Select::make('town_id')
                            ->label('Borough')
                            ->options(self::boroughOptions(...))
                            ->searchable()
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

    /** @return array<int, string> */
    public static function boroughOptions(): array
    {
        return EateryTown::query()
            ->withoutGlobalScopes()
            ->where('county_id', EateryBoroughResource::londonId())
            ->orderBy('town')
            ->pluck('town', 'id')
            ->all();
    }
}
