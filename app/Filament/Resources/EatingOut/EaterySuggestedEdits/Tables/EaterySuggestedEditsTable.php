<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EaterySuggestedEdits\Tables;

use App\Filament\Resources\EatingOut\Eateries\EateryResource;
use App\Filament\Resources\EatingOut\EaterySuggestedEdits\Actions\AcceptAndImplementEditAction;
use App\Filament\Resources\EatingOut\EaterySuggestedEdits\Actions\AcceptEditAction;
use App\Filament\Resources\EatingOut\EaterySuggestedEdits\Actions\RejectEditAction;
use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCuisine;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EaterySuggestedEdit;
use App\Models\EatingOut\EateryVenueType;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class EaterySuggestedEditsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                /** @var Builder<EaterySuggestedEdit> $query */
                $withoutScopes = fn ($query) => $query->withoutGlobalScopes();

                return $query->with([
                    'eatery' => $withoutScopes,
                    'eatery.area' => $withoutScopes,
                    'eatery.town' => $withoutScopes,
                    'eatery.county' => $withoutScopes,
                    'eatery.country' => $withoutScopes,
                    'eatery.features',
                    'eatery.openingTimes',
                    'eatery.venueType',
                    'eatery.cuisine',
                ]);
            })
            ->defaultSort('created_at', 'desc')
            ->columns([
                Stack::make([
                    Split::make([
                        TextColumn::make('eatery_name')
                            ->state(fn (EaterySuggestedEdit $record): ?string => $record->eatery?->full_name)
                            ->url(fn (EaterySuggestedEdit $record): string => EateryResource::getUrl('view', ['record' => $record->wheretoeat_id]))
                            ->size(TextSize::Large)
                            ->weight(FontWeight::Bold)
                            ->visible(fn ($livewire): bool => ! $livewire instanceof RelationManager),

                        TextColumn::make('status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Accepted' => 'success',
                                'Rejected' => 'danger',
                                default => 'info',
                            })
                            ->alignEnd()
                            ->grow(false),
                    ]),

                    Split::make([
                        TextColumn::make('field')
                            ->formatStateUsing(fn (string $state): string => Str::headline($state))
                            ->icon(Heroicon::OutlinedPencilSquare)
                            ->color('gray')
                            ->size(TextSize::Small)
                            ->grow(false),

                        TextColumn::make('created_at')
                            ->prefix('Submitted ')
                            ->dateTime()
                            ->color('gray')
                            ->size(TextSize::Small)
                            ->grow(false),
                    ])->from('md'),

                    Split::make([
                        TextColumn::make('current_value')
                            ->state(fn (EaterySuggestedEdit $record): array => self::currentValue($record))
                            ->description(fn (EaterySuggestedEdit $record): ?string => $record->field === 'info' ? null : 'Current', 'above')
                            ->listWithLineBreaks()
                            ->icon(fn (string $state, EaterySuggestedEdit $record): ?Heroicon => self::featureIcon($record, $state, suggested: false))
                            ->iconColor(fn (string $state, EaterySuggestedEdit $record): ?string => self::featureIconColour($record, $state, suggested: false))
                            ->wrap(),

                        TextColumn::make('suggested_value')
                            ->state(fn (EaterySuggestedEdit $record): array => self::suggestedValue($record))
                            ->description(fn (EaterySuggestedEdit $record): ?string => $record->field === 'info' ? null : 'Suggested', 'above')
                            ->listWithLineBreaks()
                            ->icon(fn (string $state, EaterySuggestedEdit $record): ?Heroicon => self::featureIcon($record, $state, suggested: true))
                            ->iconColor(fn (string $state, EaterySuggestedEdit $record): ?string => self::featureIconColour($record, $state, suggested: true))
                            ->wrap(),
                    ])->from('md'),
                ])->space(3),
            ])
            ->contentGrid(['md' => 1])
            ->recordActions([
                AcceptEditAction::make(),

                AcceptAndImplementEditAction::make(),

                RejectEditAction::make(),
            ]);
    }

    /** @return array<int, string> */
    public static function currentValue(EaterySuggestedEdit $edit): array
    {
        $eatery = $edit->eatery;

        assert($eatery instanceof Eatery);

        return array_values(array_filter(match ($edit->field) {
            'info' => [],
            'venue_type' => [$eatery->venueType?->venue_type],
            'cuisine' => [$eatery->cuisine?->cuisine],
            'address' => explode("\n", $eatery->address),
            'features' => self::featureNames(),
            'opening_times' => collect($eatery->openingTimes->opening_times_array ?? [])
                ->map(fn (array $times, string $day): string => Str::headline($day) . ': ' . ($times['opens'] === 'Closed' ? 'Closed' : "{$times['opens']} - {$times['closes']}"))
                ->values()
                ->all(),
            default => [$eatery->{$edit->field}],
        }));
    }

    /** @return array<int, string> */
    public static function suggestedValue(EaterySuggestedEdit $edit): array
    {
        return array_values(array_filter(match ($edit->field) {
            'venue_type' => [EateryVenueType::query()->where('venue_type', $edit->value)->orWhere('id', $edit->value)->first()?->venue_type],
            'cuisine' => [EateryCuisine::query()->where('cuisine', $edit->value)->orWhere('id', $edit->value)->first()?->cuisine],
            'address' => explode("\n", $edit->value),
            'features' => self::featureNames(),
            'opening_times' => collect(self::openingTimes($edit))
                ->map(fn (array $times): string => $times['label'] . ': ' . (($times['closed'] ?? false) ? 'Closed' : self::time($times['start']) . ' - ' . self::time($times['end'])))
                ->all(),
            default => [$edit->value],
        }));
    }

    /** @return array<int, string> */
    protected static function featureNames(): array
    {
        return EateryFeature::query()->orderBy('feature')->pluck('feature')->all();
    }

    /** @return array<int, string> */
    protected static function currentFeatures(EaterySuggestedEdit $edit): array
    {
        return $edit->eatery?->features->pluck('feature')->all() ?? [];
    }

    /** @return array<int, string> */
    protected static function suggestedFeatures(EaterySuggestedEdit $edit): array
    {
        /** @var array<int, array{label: string, selected: bool}> $features */
        $features = json_decode($edit->value, true) ?? [];

        return collect($features)
            ->filter(fn (array $feature): bool => (bool) $feature['selected'])
            ->map(fn (array $feature): string => $feature['label'])
            ->values()
            ->all();
    }

    public static function featureIsSelected(EaterySuggestedEdit $edit, string $feature, bool $suggested): ?bool
    {
        if ($edit->field !== 'features') {
            return null;
        }

        return in_array($feature, $suggested ? self::suggestedFeatures($edit) : self::currentFeatures($edit));
    }

    protected static function featureIcon(EaterySuggestedEdit $edit, string $feature, bool $suggested): ?Heroicon
    {
        return match (self::featureIsSelected($edit, $feature, $suggested)) {
            true => Heroicon::OutlinedCheckCircle,
            false => Heroicon::OutlinedXCircle,
            null => null,
        };
    }

    protected static function featureIconColour(EaterySuggestedEdit $edit, string $feature, bool $suggested): ?string
    {
        return match (self::featureIsSelected($edit, $feature, $suggested)) {
            true => 'success',
            false => 'danger',
            null => null,
        };
    }

    /** @return array<int, array{label: string, closed?: bool, start: array<int, int|null>, end: array<int, int|null>}> */
    protected static function openingTimes(EaterySuggestedEdit $edit): array
    {
        return json_decode($edit->value, true) ?? [];
    }

    /** @param array<int, int|null> $time */
    protected static function time(array $time): string
    {
        return collect($time)->take(2)->map(fn (?int $part): string => Str::padLeft((string) $part, 2, '0'))->join(':');
    }
}
