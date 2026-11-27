<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryReports\Tables;

use App\Filament\Resources\EatingOut\Eateries\EateryResource;
use App\Filament\Resources\EatingOut\EateryReports\Actions\CompleteReportAction;
use App\Filament\Resources\EatingOut\EateryReports\Actions\IgnoreReportAction;
use App\Models\EatingOut\EateryReport;
use App\Models\EatingOut\NationwideBranch;
use Filament\Actions\DeleteAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EateryReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                /** @var Builder<EateryReport> $query */
                $withoutScopes = fn ($query) => $query->withoutGlobalScopes();

                return $query->with([
                    'eatery' => $withoutScopes,
                    'eatery.area' => $withoutScopes,
                    'eatery.town' => $withoutScopes,
                    'eatery.county' => $withoutScopes,
                    'eatery.country' => $withoutScopes,
                    'branch' => $withoutScopes,
                    'branch.eatery' => $withoutScopes,
                    'branch.area' => $withoutScopes,
                    'branch.town' => $withoutScopes,
                    'branch.county' => $withoutScopes,
                    'branch.country' => $withoutScopes,
                ]);
            })
            ->defaultSort(function (Builder $query): Builder {
                /** @var Builder<EateryReport> $query */
                return $query
                    ->orderByRaw('(completed = 1 or ignored = 1) asc')
                    ->orderByDesc('created_at');
            })
            ->columns([
                Stack::make([
                    Split::make([
                        Stack::make([
                            TextColumn::make('eatery_name')
                                ->state(fn (EateryReport $record): string => $record->eatery->full_name)
                                ->url(fn (EateryReport $record): string => EateryResource::getUrl('view', ['record' => $record->wheretoeat_id]))
                                ->size(TextSize::Large)
                                ->weight(FontWeight::Bold)
                                ->visible(fn ($livewire): bool => ! $livewire instanceof RelationManager),

                            TextColumn::make('branch_full_name')
                                ->state(fn (EateryReport $record): ?string => $record->branch?->full_name)
                                ->color('gray')
                                ->visible(fn ($livewire): bool => ! ($livewire instanceof RelationManager && $livewire->getOwnerRecord() instanceof NationwideBranch)),
                        ]),

                        TextColumn::make('status')
                            ->state(fn (EateryReport $record): string => match (true) {
                                $record->completed => 'Completed',
                                $record->ignored => 'Ignored',
                                default => 'Pending',
                            })
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'Completed' => 'success',
                                'Ignored' => 'gray',
                                default => 'danger',
                            })
                            ->alignEnd()
                            ->grow(false),
                    ]),

                    Split::make([
                        TextColumn::make('branch_name')
                            ->icon(Heroicon::OutlinedMapPin)
                            ->prefix('Branch entered: ')
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

                    TextColumn::make('details')->wrap(),
                ])->space(3),
            ])
            ->contentGrid(['md' => 1])
            ->filters([
                TernaryFilter::make('completed'),
                TernaryFilter::make('ignored'),
            ])
            ->recordActions([
                CompleteReportAction::make(),

                IgnoreReportAction::make(),

                DeleteAction::make(),
            ]);
    }
}
