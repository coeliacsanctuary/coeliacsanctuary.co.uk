<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\SealiacOverviews\Tables;

use App\Models\EatingOut\Eatery;
use App\Models\EatingOut\NationwideBranch;
use App\Models\SealiacOverview;
use App\Models\Shop\ShopProduct;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SealiacOverviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('model_type')
                    ->label('Type')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn (string $state): string => self::typeLabel($state)),

                TextColumn::make('model_name')
                    ->label('Model')
                    ->state(fn (SealiacOverview $record): ?string => self::modelName($record))
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query->whereHasMorph(
                        'model',
                        [Eatery::class, NationwideBranch::class, ShopProduct::class],
                        fn (Builder $query, string $type) => $query
                            ->withoutGlobalScopes()
                            ->where($type === ShopProduct::class ? 'title' : 'name', 'like', "%{$search}%"),
                    )),

                TextColumn::make('status')
                    ->badge()
                    ->state(fn (SealiacOverview $record): string => $record->invalidated ? 'Invalidated' : 'Active')
                    ->color(fn (string $state): string => $state === 'Invalidated' ? 'danger' : 'success'),

                TextColumn::make('thumbs_up')
                    ->label('Thumbs Up Count')
                    ->icon(Heroicon::OutlinedHandThumbUp)
                    ->iconColor('success')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('thumbs_down')
                    ->label('Thumbs Down Count')
                    ->icon(Heroicon::OutlinedHandThumbDown)
                    ->iconColor('danger')
                    ->numeric()
                    ->sortable(),

                // Both thumbs columns are unsigned, so the subtraction has to be cast or it underflows on a negative rating.
                TextColumn::make('rating')
                    ->badge()
                    ->state(fn (SealiacOverview $record): int => self::rating($record))
                    ->color(fn (int $state): string => self::ratingColour($state))
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderByRaw("(cast(thumbs_up as signed) - cast(thumbs_down as signed)) {$direction}")),

                TextColumn::make('overview')
                    ->lineClamp(3)
                    ->wrap(),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('Read More...')
                    ->icon(Heroicon::Eye)
                    ->modalHeading('Sealiac Overview')
                    ->modalWidth(Width::ThreeExtraLarge)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->schema([
                        Grid::make(3)->schema([
                            TextEntry::make('model_type')
                                ->label('Type')
                                ->badge()
                                ->color('gray')
                                ->formatStateUsing(fn (string $state): string => self::typeLabel($state)),

                            TextEntry::make('model_name')
                                ->label('Model')
                                ->columnSpan(2)
                                ->state(fn (SealiacOverview $record): ?string => self::modelName($record)),

                            TextEntry::make('status')
                                ->badge()
                                ->state(fn (SealiacOverview $record): string => $record->invalidated ? 'Invalidated' : 'Active')
                                ->color(fn (string $state): string => $state === 'Invalidated' ? 'danger' : 'success'),

                            TextEntry::make('thumbs_up')
                                ->label('Thumbs Up Count')
                                ->icon(Heroicon::OutlinedHandThumbUp)
                                ->iconColor('success')
                                ->numeric(),

                            TextEntry::make('thumbs_down')
                                ->label('Thumbs Down Count')
                                ->icon(Heroicon::OutlinedHandThumbDown)
                                ->iconColor('danger')
                                ->numeric(),
                        ]),

                        TextEntry::make('overview')
                            ->hiddenLabel()
                            ->columnSpanFull()
                            ->html()
                            ->formatStateUsing(fn (string $state): string => nl2br($state)),
                    ]),

                Action::make('invalidate')
                    ->icon(Heroicon::XMark)
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalDescription('Are you sure you want to invalidate this overview?')
                    ->visible(fn (SealiacOverview $record): bool => ! $record->invalidated)
                    ->action(fn (SealiacOverview $record) => $record->update(['invalidated' => true])),
            ]);
    }

    protected static function typeLabel(string $modelType): string
    {
        return match ($modelType) {
            Eatery::class => 'Eatery',
            NationwideBranch::class => 'Nationwide Branch',
            ShopProduct::class => 'Product',
            default => class_basename($modelType),
        };
    }

    protected static function modelName(SealiacOverview $record): ?string
    {
        return match ($record->model_type) {
            Eatery::class => $record->model?->name,
            NationwideBranch::class => $record->model?->name ?: $record->model?->eatery?->name,
            ShopProduct::class => $record->model?->title,
            default => null,
        };
    }

    protected static function rating(SealiacOverview $record): int
    {
        return $record->thumbs_up - $record->thumbs_down;
    }

    protected static function ratingColour(int $rating): string
    {
        if ($rating > 0) {
            return 'success';
        }

        if ($rating < 0) {
            return 'danger';
        }

        return 'gray';
    }
}
