<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryAlerts\Tables;

use App\Filament\Resources\EatingOut\Eateries\EateryResource;
use App\Filament\Resources\EatingOut\EateryAlerts\Actions\CompleteAlertAction;
use App\Filament\Resources\EatingOut\EateryAlerts\Actions\IgnoreAlertAction;
use App\Filament\Resources\EatingOut\EateryAlerts\Actions\IgnoreAlertForSixMonthsAction;
use App\Models\EatingOut\EateryAlert;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EateryAlertsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                /** @var Builder<EateryAlert> $query */
                $withoutScopes = fn ($query) => $query->withoutGlobalScopes();

                return $query->with([
                    'eatery' => $withoutScopes,
                    'eatery.area' => $withoutScopes,
                    'eatery.town' => $withoutScopes,
                    'eatery.county' => $withoutScopes,
                    'eatery.country' => $withoutScopes,
                ]);
            })
            ->defaultSort(function (Builder $query): Builder {
                /** @var Builder<EateryAlert> $query */
                return $query
                    ->orderByRaw('(completed = 1 or ignored = 1) asc')
                    ->orderByDesc('created_at');
            })
            ->columns([
                TextColumn::make('eatery_name')
                    ->label('Eatery')
                    ->state(fn (EateryAlert $record): ?string => $record->eatery?->full_name)
                    ->url(fn (EateryAlert $record): string => EateryResource::getUrl('view', ['record' => $record->wheretoeat_id])),

                TextColumn::make('type')
                    ->label('Check Type'),

                TextColumn::make('details'),

                TextColumn::make('eatery.website')
                    ->label('Website')
                    ->url(fn (EateryAlert $record): ?string => $record->eatery?->website)
                    ->openUrlInNewTab(),

                IconColumn::make('completed')->boolean(),

                IconColumn::make('ignored')->boolean(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime(),
            ])
            ->filters([
                TernaryFilter::make('completed'),
                TernaryFilter::make('ignored'),
            ])
            ->recordActions([
                ActionGroup::make([
                    CompleteAlertAction::make(),

                    IgnoreAlertAction::make(),

                    IgnoreAlertForSixMonthsAction::make(),

                    DeleteAction::make(),
                ]),
            ]);
    }
}
