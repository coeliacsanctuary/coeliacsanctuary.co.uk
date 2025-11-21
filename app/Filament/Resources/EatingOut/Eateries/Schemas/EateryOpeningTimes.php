<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\Eateries\Schemas;

use App\Models\EatingOut\Eatery;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Text;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class EateryOpeningTimes
{
    public static function make(): Section
    {
        return Section::make('Opening Times')
            ->columnSpanFull()
            ->schema([
                Toggle::make('no_opening_times')
                    ->label('Unknown / Not Set')
                    ->default(true)
                    ->formatStateUsing(fn (?Eatery $record): bool => ! $record || $record->openingTimes === null)
                    ->dehydrated(false)
                    ->live(),

                Grid::make()
                    ->hidden(fn (Get $get): bool => $get('no_opening_times'))
                    ->columnSpanFull()
                    ->columns(8)
                    ->extraAttributes(['class' => 'items-center'])
                    ->relationship('openingTimes')
                    ->formatStateUsing(function (?Eatery $record) {
                        $openingTimes = $record?->openingTimes;

                        if ( ! $openingTimes) {
                            return [];
                        }

                        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

                        return collect($days)
                            ->map(function ($day) use ($openingTimes) {
                                $start = explode(':', $openingTimes->{"{$day}_start"} ?? '');
                                $end = explode(':', $openingTimes->{"{$day}_end"} ?? '');

                                return [
                                    "{$day}_closed" => $openingTimes->{"{$day}_start"} === null,
                                    "{$day}_start_h" => $openingTimes->{"{$day}_start"} ? (int) $start[0] : 0,
                                    "{$day}_start_m" => $openingTimes->{"{$day}_start"} ? (int) $start[1] : 0,
                                    "{$day}_end_h" => $openingTimes->{"{$day}_end"} ? (int) $end[0] : 0,
                                    "{$day}_end_m" => $openingTimes->{"{$day}_end"} ? (int) $end[1] : 0,
                                ];
                            })
                            ->collapse()
                            ->toArray();
                    })
                    ->mutateRelationshipDataBeforeSaveUsing(function (array $state): array {
                        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

                        return collect($days)
                            ->map(function (string $day) use ($state) {
                                $start = null;
                                $end = null;

                                if ( ! Arr::boolean($state, "{$day}_closed")) {
                                    $start = implode(':', [
                                        Str::padLeft(Arr::get($state, "{$day}_start_h", 0), 2, '0'),
                                        Str::padLeft(Arr::get($state, "{$day}_start_m", 0), 2, '0'),
                                        '00',
                                    ]);

                                    $end = implode(':', [
                                        Str::padLeft(Arr::get($state, "{$day}_end_h", 0), 2, '0'),
                                        Str::padLeft(Arr::get($state, "{$day}_end_m", 0), 2, '0'),
                                        '00',
                                    ]);
                                }

                                return [
                                    "{$day}_start" => $start,
                                    "{$day}_end" => $end,
                                ];
                            })
                            ->collapse()
                            ->toArray();
                    })
                    ->schema(function () {
                        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

                        return collect($days)
                            ->map(fn (string $day) => [
                                Text::make(ucwords($day))->extraAttributes(['style' => 'font-weight: 600'])->columnSpan(5),

                                Toggle::make($day . '_closed')
                                    ->dehydrated(false)
                                    ->label('Closed')
                                    ->inlineLabel()
                                    ->live(),

                                Grid::make()
                                    ->columns(2)
                                    ->schema([
                                        Select::make($day . '_start_h')
                                            ->options(range(0, 23))
                                            ->hiddenLabel()
                                            ->default(0)
                                            ->required(fn (Get $get) => ! $get($day . '_closed'))
                                            ->disabled(fn (Get $get) => $get($day . '_closed')),

                                        Select::make($day . '_start_m')
                                            ->options([0 => '00', 15 => '15', 30 => '30', 45 => '45'])
                                            ->hiddenLabel()
                                            ->default(0)
                                            ->required(fn (Get $get) => ! $get($day . '_closed'))
                                            ->disabled(fn (Get $get) => $get($day . '_closed')),
                                    ]),

                                Grid::make()
                                    ->columns(2)
                                    ->schema([
                                        Select::make($day . '_end_h')
                                            ->options(range(0, 23))
                                            ->hiddenLabel()
                                            ->default(0)
                                            ->required(fn (Get $get) => ! $get($day . '_closed'))
                                            ->disabled(fn (Get $get) => $get($day . '_closed')),

                                        Select::make($day . '_end_m')
                                            ->options([0 => '00', 15 => '15', 30 => '30', 45 => '45'])
                                            ->hiddenLabel()
                                            ->default(0)
                                            ->required(fn (Get $get) => ! $get($day . '_closed'))
                                            ->disabled(fn (Get $get) => $get($day . '_closed')),
                                    ]),
                            ])
                            ->flatten()
                            ->toArray();
                    }),
            ]);
    }
}
