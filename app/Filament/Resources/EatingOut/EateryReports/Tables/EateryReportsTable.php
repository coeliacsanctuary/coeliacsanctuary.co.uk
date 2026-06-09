<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryReports\Tables;

use App\Models\EatingOut\EateryReport;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class EateryReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn ($query) => $query
                    ->with([
                        'eatery' => fn ($query) => $query->withoutGlobalScopes(),
                        'branch' => fn ($query) => $query->withoutGlobalScopes(),
                    ])
                    ->reorder('created_at', 'desc')
            )
            ->columns([
                Grid::make()
                    ->columns(3)
                    ->schema([
                        Stack::make([
                            TextColumn::make('eatery.name')
                                ->size(TextSize::Large)
                                ->weight(FontWeight::Bold),

                            TextColumn::make('branch.name')
                                ->size(TextSize::Medium),
                        ])->columnSpan(2),

                        Stack::make([
                            TextColumn::make('completed')
                                ->visible(fn (EateryReport $record) => ! $record->completed && ! $record->ignored)
                                ->badge()
                                ->color('danger')
                                ->alignRight()
                                ->size(TextSize::Large)
                                ->formatStateUsing(fn () => 'New!'),

                            TextColumn::make('completed')
                                ->prefix('Completed: ')
                                ->icon(fn ($state) => ($state ? Heroicon::OutlinedCheckCircle : Heroicon::OutlinedXCircle))
                                ->iconColor(fn ($state) => ($state ? 'success' : 'danger'))
                                ->formatStateUsing(fn () => '')
                                ->iconPosition(IconPosition::After)
                                ->alignRight(),

                            TextColumn::make('ignored')
                                ->prefix('Ignored: ')
                                ->icon(fn ($state) => ($state ? Heroicon::OutlinedCheckCircle : Heroicon::OutlinedXCircle))
                                ->iconColor(fn ($state) => ($state ? 'success' : 'danger'))
                                ->formatStateUsing(fn () => '')
                                ->iconPosition(IconPosition::After)
                                ->alignRight(),
                        ]),

                        TextColumn::make('id')
                            ->columnSpanFull()
                            ->html()
                            ->formatStateUsing(fn () => '<hr  />'),

                        TextColumn::make('details')->wrap()->columnSpanFull(),

                        TextColumn::make('wheretoeat_id')
                            ->columnSpanFull()
                            ->html()
                            ->formatStateUsing(fn () => '<hr  />'),

                        Split::make([
                            TextColumn::make('created_at')
                                ->prefix('Submitted: ')
                                ->dateTime(),
                        ])->columnSpanFull(),
                    ]),
            ])
            ->contentGrid(['md' => 1])
            ->filters([
                TernaryFilter::make('completed'),
                TernaryFilter::make('ignored'),
            ])
            ->recordActions([
                Action::make('complete')
                    ->label('Complete')
                    ->requiresConfirmation()
                    ->disabled(fn (EateryReport $record) => $record->completed || $record->ignored)
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->color('success')
                    ->action(function (EateryReport $record): void {
                        $record->update(['completed' => true]);
                    }),

                Action::make('ignore')
                    ->label('Ignore')
                    ->requiresConfirmation()
                    ->disabled(fn (EateryReport $record) => $record->completed || $record->ignored)
                    ->icon(Heroicon::OutlinedBellSlash)
                    ->color('warning')
                    ->action(function (EateryReport $record): void {
                        $record->update(['ignored' => true]);
                    }),

                DeleteAction::make(),
            ]);
    }
}
