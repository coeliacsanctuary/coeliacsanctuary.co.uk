<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryReviews\Tables;

use App\Filament\Resources\EatingOut\EateryReviews\Actions\ApproveReviewAction;
use App\Filament\Resources\EatingOut\EateryReviews\Actions\SetReviewBranchAction;
use App\Models\EatingOut\EateryReview;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Schemas\Components\Image;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EateryReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                /** @var Builder<EateryReview> $query */
                $withoutScopes = fn ($query) => $query->withoutGlobalScopes();

                return $query
                    ->with([
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
                        'images',
                    ])
                    ->withCount('images');
            })
            ->defaultSort('created_at', 'desc')
            ->searchable()
            ->searchUsing(function (Builder $query, string $search): Builder {
                /** @var Builder<EateryReview> $query */
                return $query->whereKey($search);
            })
            ->columns([
                Stack::make([
                    Split::make([
                        Stack::make([
                            TextColumn::make('eatery_name')
                                ->state(fn (EateryReview $record): ?string => $record->eatery?->full_name)
                                ->size(TextSize::Large)
                                ->weight(FontWeight::Bold),

                            TextColumn::make('branch_full_name')
                                ->state(fn (EateryReview $record): ?string => $record->branch?->full_name)
                                ->color('gray'),
                        ]),

                        Stack::make([
                            TextColumn::make('approved')
                                ->badge()
                                ->formatStateUsing(fn (bool $state): string => $state ? 'Approved' : 'Pending')
                                ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                                ->grow(false),

                            TextColumn::make('method')
                                ->badge()
                                ->color('gray')
                                ->formatStateUsing(fn (string $state): string => match ($state) {
                                    'website' => 'Website',
                                    'app' => 'App',
                                    default => $state,
                                })
                                ->grow(false),
                        ])
                            ->space(1)
                            ->alignment(Alignment::End)
                            ->grow(false),
                    ]),

                    Split::make([
                        TextColumn::make('name')
                            ->icon(Heroicon::OutlinedUser)
                            ->color('gray')
                            ->size(TextSize::Small)
                            ->grow(false),

                        TextColumn::make('email')
                            ->icon(Heroicon::OutlinedEnvelope)
                            ->color('gray')
                            ->size(TextSize::Small)
                            ->copyable()
                            ->grow(false),

                        TextColumn::make('ip')
                            ->icon(Heroicon::OutlinedGlobeAlt)
                            ->color('gray')
                            ->size(TextSize::Small)
                            ->grow(false),

                        TextColumn::make('branch_name')
                            ->icon(Heroicon::OutlinedMapPin)
                            ->prefix('Branch entered: ')
                            ->color('gray')
                            ->size(TextSize::Small)
                            ->grow(false),
                    ])->from('md'),

                    Split::make([
                        TextColumn::make('rating')
                            ->icon(Heroicon::Star)
                            ->iconColor('warning')
                            ->prefix('Rating: ')
                            ->suffix('/5')
                            ->grow(false),

                        TextColumn::make('how_expensive')
                            ->icon(Heroicon::OutlinedCurrencyPound)
                            ->prefix('How Expensive: ')
                            ->suffix('/5')
                            ->grow(false),

                        TextColumn::make('food_rating')
                            ->prefix('Food: ')
                            ->formatStateUsing(fn (string $state): string => ucfirst($state))
                            ->grow(false),

                        TextColumn::make('service_rating')
                            ->prefix('Service: ')
                            ->formatStateUsing(fn (string $state): string => ucfirst($state))
                            ->grow(false),
                    ])->from('md'),

                    TextColumn::make('review')->wrap(),

                    Split::make([
                        TextColumn::make('created_at')
                            ->prefix('Submitted ')
                            ->dateTime()
                            ->color('gray')
                            ->size(TextSize::Small)
                            ->grow(false),

                        TextColumn::make('images_count')
                            ->icon(Heroicon::OutlinedPhoto)
                            ->suffix(' images')
                            ->color('gray')
                            ->size(TextSize::Small)
                            ->grow(false),
                    ]),
                ])->space(3),

                Panel::make([
                    Grid::make()
                        ->columns(6)
                        ->schema(function (): array {
                            $columns = [];

                            foreach (range(0, 5) as $i) {
                                $columns[] = ImageColumn::make("image-{$i}")
                                    ->visible(fn (?EateryReview $record): bool => isset($record?->images[$i]))
                                    ->getStateUsing(fn (?EateryReview $record): ?string => $record?->images->get($i)?->thumb)
                                    ->imageSize(150)
                                    ->action(
                                        Action::make("view-image-{$i}")
                                            ->schema([
                                                Image::make(fn (?EateryReview $record): ?string => $record?->images->get($i)?->path, '')->imageSize('100%'),
                                            ])
                                            ->modalSubmitAction(false)
                                            ->modalCancelAction(false),
                                    );
                            }

                            return $columns;
                        }),
                ])->visible(fn (?EateryReview $record): bool => $record?->images_count > 0),
            ])
            ->contentGrid(['md' => 1])
            ->filters([
                TernaryFilter::make('approved'),

                SelectFilter::make('method')
                    ->options([
                        'website' => 'Website',
                        'app' => 'App',
                    ]),
            ])
            ->recordActions([
                ApproveReviewAction::make(),

                SetReviewBranchAction::make(),

                DeleteAction::make(),
            ]);
    }
}
