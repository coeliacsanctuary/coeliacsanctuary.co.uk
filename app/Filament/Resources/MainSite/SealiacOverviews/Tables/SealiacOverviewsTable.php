<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\SealiacOverviews\Tables;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Models\SealiacOverview;
use App\Models\Shop\ShopProduct;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SealiacOverviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->latest()->with(['model']))
            ->columns([
                TextColumn::make('model')
                    ->searchable()
                    ->prefix(fn (SealiacOverview $record) => class_basename($record->model_type) . ' - ')
                    ->formatStateUsing(fn (SealiacOverview $record) => match ($record->model::class) {
                        ShopProduct::class => $record->model->title,
                        Eatery::class => $record->model->full_name,
                        NationwideBranch::class => $record->model->full_name ?? $record->model->eatery->full_name,
                        default => null,
                    })
                    ->url(fn (SealiacOverview $record) => match ($record->model::class) {
                        ShopProduct::class => '', // resource
                        Eatery::class => '', // resource
                        default => null,
                    })
                    ->openUrlInNewTab(),

                TextColumn::make('status')
                    ->badge()
                    ->getStateUsing(fn (SealiacOverview $record) => $record->invalidated ? 'Invalidated' : 'Active')
                    ->color(fn (SealiacOverview $record) => match ($record->invalidated) {
                        true => 'danger',
                        false => 'success',
                    }),

                TextColumn::make('thumbs_up')
                    ->sortable()
                    ->numeric(),

                TextColumn::make('thumbs_down')
                    ->sortable()
                    ->numeric(),

                TextColumn::make('rating')
                    ->getStateUsing(fn (SealiacOverview $record) => $record->thumbs_up - $record->thumbs_down)
                    ->sortable()
                    ->numeric(),

                TextColumn::make('overview')
                    ->lineClamp(3)
                    ->wrap(),
            ])
            ->recordActions([
                Action::make('view')
                    ->icon(Heroicon::Eye)
                    ->label('Read More...')
                    ->schema([
                        TextEntry::make('overview')
                            ->wrap()
                            ->html()
                            ->formatStateUsing(fn (string $state) => nl2br($state)),
                    ]),

                Action::make('invalidate')
                    ->requiresConfirmation()
                    ->icon(Heroicon::XMark)
                    ->color('danger')
                    ->modalDescription('Are you sure you want to invalidate this overview?')
                    ->disabled(fn (SealiacOverview $record) => $record->invalidated)
                    ->action(fn (SealiacOverview $record) => $record->update(['invalidated' => true])),
            ]);
    }
}
