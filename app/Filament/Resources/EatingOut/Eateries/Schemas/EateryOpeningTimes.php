<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\Eateries\Schemas;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryOpeningTimes as EateryOpeningTimesModel;
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
                    ->formatStateUsing(fn (?Eatery $record, ?bool $state): bool => $record instanceof Eatery ? $record->openingTimes === null : $state ?? true)
                    ->dehydrated(false)
                    ->live(),

                Grid::make()
                    ->hidden(fn (Get $get): bool => $get('no_opening_times'))
                    ->columnSpanFull()
                    ->columns(8)
                    ->extraAttributes(['class' => 'items-center'])
                    ->relationship('openingTimes')
                    ->formatStateUsing(fn (?Eatery $record, mixed $state): array => ! $record instanceof Eatery && is_array($state) && $state !== []
                        ? [...self::toFormState(null), ...array_filter($state, fn (mixed $value): bool => $value !== null)]
                        : self::toFormState($record?->openingTimes))
                    ->mutateRelationshipDataBeforeCreateUsing(fn (array $state): array => self::fromFormState($state))
                    ->mutateRelationshipDataBeforeSaveUsing(fn (array $state): array => self::fromFormState($state))
                    ->schema(fn () => collect(self::days())
                        ->map(fn (string $day): array => [
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
                                        ->options(array_map(strval(...), range(0, 23)))
                                        ->hiddenLabel()
                                        ->default(0)
                                        ->required(fn (Get $get): bool => ! $get($day . '_closed'))
                                        ->disabled(fn (Get $get): mixed => $get($day . '_closed')),

                                    Select::make($day . '_start_m')
                                        ->options([0 => '00', 15 => '15', 30 => '30', 45 => '45'])
                                        ->hiddenLabel()
                                        ->default(0)
                                        ->required(fn (Get $get): bool => ! $get($day . '_closed'))
                                        ->disabled(fn (Get $get): mixed => $get($day . '_closed')),
                                ]),

                            Grid::make()
                                ->columns(2)
                                ->schema([
                                    Select::make($day . '_end_h')
                                        ->options(array_map(strval(...), range(0, 23)))
                                        ->hiddenLabel()
                                        ->default(0)
                                        ->required(fn (Get $get): bool => ! $get($day . '_closed'))
                                        ->disabled(fn (Get $get): mixed => $get($day . '_closed')),

                                    Select::make($day . '_end_m')
                                        ->options([0 => '00', 15 => '15', 30 => '30', 45 => '45'])
                                        ->hiddenLabel()
                                        ->default(0)
                                        ->required(fn (Get $get): bool => ! $get($day . '_closed'))
                                        ->disabled(fn (Get $get): mixed => $get($day . '_closed')),
                                ]),
                        ])
                        ->flatten()
                        ->toArray()),
            ]);
    }

    /** @return array<string, bool|int> */
    public static function toFormState(?EateryOpeningTimesModel $openingTimes): array
    {
        return collect(self::days())
            ->mapWithKeys(function (string $day) use ($openingTimes): array {
                $start = $openingTimes?->{"{$day}_start"};
                $end = $openingTimes?->{"{$day}_end"};

                [$startHour, $startMinute] = explode(':', $start ?? '0:0');
                [$endHour, $endMinute] = explode(':', $end ?? '0:0');

                return [
                    "{$day}_closed" => $openingTimes instanceof EateryOpeningTimesModel && $start === null,
                    "{$day}_start_h" => (int) $startHour,
                    "{$day}_start_m" => (int) $startMinute,
                    "{$day}_end_h" => (int) $endHour,
                    "{$day}_end_m" => (int) $endMinute,
                ];
            })
            ->all();
    }

    /**
     * @param  array<string, mixed>  $state
     * @return array<string, string|null>
     */
    protected static function fromFormState(array $state): array
    {
        return collect(self::days())
            ->mapWithKeys(fn (string $day): array => Arr::get($state, "{$day}_closed")
                ? ["{$day}_start" => null, "{$day}_end" => null]
                : [
                    "{$day}_start" => self::time($state, "{$day}_start"),
                    "{$day}_end" => self::time($state, "{$day}_end"),
                ])
            ->all();
    }

    /** @param array<string, mixed> $state */
    protected static function time(array $state, string $key): string
    {
        return implode(':', [
            Str::padLeft((string) Arr::get($state, "{$key}_h", 0), 2, '0'),
            Str::padLeft((string) Arr::get($state, "{$key}_m", 0), 2, '0'),
            '00',
        ]);
    }

    /** @return array<int, string> */
    protected static function days(): array
    {
        return ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    }
}
