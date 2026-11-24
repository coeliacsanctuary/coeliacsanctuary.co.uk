<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EaterySuggestedEdits\Tables;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\EateryCuisine;
use App\Models\EatingOut\EateryFeature;
use App\Models\EatingOut\EaterySuggestedEdit;
use App\Models\EatingOut\EateryVenueType;
use App\Support\EatingOut\SuggestEdits\SuggestedEditProcessor;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class EaterySuggestedEditsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn ($query) => $query
                    ->with(['eatery' => fn ($query) => $query->withoutGlobalScopes()->with(['features', 'openingTimes', 'venueType', 'cuisine'])])
                    ->reorder('created_at', 'desc')
            )
            ->columns([
                TextColumn::make('eatery.name')
                    ->visible(fn ($livewire): bool => ! $livewire instanceof RelationManager),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => self::statusColour($state)),

                TextColumn::make('field')->formatStateUsing(fn (string $state): string => Str::headline($state)),

                TextColumn::make('value')
                    ->state(fn (EaterySuggestedEdit $record): ?string => in_array($record->field, ['opening_times', 'features']) ? null : $record->value)
                    ->limit(75),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime(),
            ])
            ->recordActions([
                Action::make('view')
                    ->icon(Heroicon::Eye)
                    ->color('gray')
                    ->modalHeading('Suggested Edit')
                    ->modalWidth(Width::FourExtraLarge)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('status')
                                ->badge()
                                ->color(fn (string $state): string => self::statusColour($state)),

                            TextEntry::make('field')->formatStateUsing(fn (string $state): string => Str::headline($state)),

                            TextEntry::make('created_at')
                                ->label('Created')
                                ->dateTime(),
                        ]),

                        Grid::make()->schema([
                            TextEntry::make('current_value')
                                ->state(fn (EaterySuggestedEdit $record): array => self::currentValue($record))
                                ->listWithLineBreaks()
                                ->placeholder('-'),

                            TextEntry::make('suggested_value')
                                ->state(fn (EaterySuggestedEdit $record): array => self::suggestedValue($record))
                                ->listWithLineBreaks()
                                ->placeholder('-'),
                        ]),
                    ]),

                Action::make('accept')
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->visible(fn (EaterySuggestedEdit $record): bool => self::isNew($record))
                    ->action(fn (EaterySuggestedEdit $record) => $record->update(['accepted' => true])),

                Action::make('acceptAndImplement')
                    ->label('Accept and Implement')
                    ->icon(Heroicon::OutlinedCheckBadge)
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (EaterySuggestedEdit $record): bool => self::isNew($record) && $record->field !== 'info')
                    ->action(function (EaterySuggestedEdit $record): void {
                        $eatery = $record->eatery;

                        assert($eatery instanceof Eatery);

                        app(SuggestedEditProcessor::class)
                            ->resolveEditableField($record->field, $record->value)
                            ->commitSuggestedValue($eatery);

                        $record->update(['accepted' => true]);
                    }),

                Action::make('reject')
                    ->icon(Heroicon::OutlinedXCircle)
                    ->color('danger')
                    ->visible(fn (EaterySuggestedEdit $record): bool => self::isNew($record))
                    ->action(fn (EaterySuggestedEdit $record) => $record->update(['rejected' => true])),
            ]);
    }

    protected static function isNew(EaterySuggestedEdit $edit): bool
    {
        return ! $edit->accepted && ! $edit->rejected;
    }

    protected static function statusColour(string $status): string
    {
        return match ($status) {
            'Accepted' => 'success',
            'Rejected' => 'danger',
            default => 'info',
        };
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
            'features' => $eatery->features->map(fn (EateryFeature $feature): string => $feature->feature)->all(),
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
            'features' => collect(self::features($edit))
                ->filter(fn (array $feature): bool => (bool) $feature['selected'])
                ->map(fn (array $feature): string => $feature['label'])
                ->values()
                ->all(),
            'opening_times' => collect(self::openingTimes($edit))
                ->map(fn (array $times): string => $times['label'] . ': ' . (($times['closed'] ?? false) ? 'Closed' : self::time($times['start']) . ' - ' . self::time($times['end'])))
                ->all(),
            default => [$edit->value],
        }));
    }

    /** @return array<int, array{label: string, selected: bool}> */
    protected static function features(EaterySuggestedEdit $edit): array
    {
        return json_decode($edit->value, true) ?? [];
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
