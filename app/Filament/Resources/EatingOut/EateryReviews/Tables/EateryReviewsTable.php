<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryReviews\Tables;

use App\Models\EatingOut\EateryReview;
use App\Notifications\EatingOut\EateryReviewApprovedNotification;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Schemas\Components\Image;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Panel;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Notifications\AnonymousNotifiable;

class EateryReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn ($query) => $query
                    ->with([
                        'eatery' => fn ($query) => $query->withoutGlobalScopes(),
                        'branch' => fn ($query) => $query->withoutGlobalScopes(),
                        'images',
                    ])
                    ->withCount(['images' => fn ($query) => $query->withoutGlobalScopes()])
                    ->where('admin_review', false)
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
                            TextColumn::make('approved')
                                ->visible(fn (EateryReview $record) => ! $record->approved)
                                ->badge()
                                ->color('danger')
                                ->alignRight()
                                ->size(TextSize::Large)
                                ->formatStateUsing(fn () => 'New!'),

                            TextColumn::make('method')->prefix('Source: ')->alignRight(),
                        ]),

                        TextColumn::make('id')
                            ->columnSpanFull()
                            ->html()
                            ->formatStateUsing(fn () => '<hr  />'),

                        Stack::make([
                            TextColumn::make('rating')->prefix('Rating: '),
                            TextColumn::make('how_expensive')->prefix('How Expensive: '),
                            TextColumn::make('food_rating')->prefix('Food: '),
                            TextColumn::make('service_rating')->prefix('Service: '),
                        ])->columnSpan(1),

                        TextColumn::make('review')->wrap()->columnSpan(2),

                        TextColumn::make('wheretoeat_id')
                            ->columnSpanFull()
                            ->html()
                            ->formatStateUsing(fn () => '<hr  />'),

                        Split::make([
                            TextColumn::make('images_count')
                                ->prefix('Images: '),

                            TextColumn::make('created_at')
                                ->prefix('Submitted: ')
                                ->dateTime(),

                            TextColumn::make('approved')
                                ->prefix('Approved: ')
                                ->icon(fn ($state) => ($state ? Heroicon::OutlinedCheckCircle : Heroicon::OutlinedXCircle))
                                ->iconColor(fn ($state) => ($state ? 'success' : 'danger'))
                                ->formatStateUsing(fn () => '')
                                ->iconPosition(IconPosition::After),
                        ])->columnSpanFull(),

                    ]),

                Panel::make([
                    Grid::make()
                        ->columns(6)
                        ->schema(function () {
                            $columns = [];

                            foreach (range(0, 5) as $i) {
                                $columns[] = ImageColumn::make("image-{$i}")
                                    ->visible(fn (?EateryReview $record) => isset($record?->images[$i]))
                                    ->getStateUsing(fn (?EateryReview $record) => $record?->images[$i]->thumb)
                                    ->imageSize(150)
                                    ->action(
                                        Action::make("view-image-{$i}")
                                            ->schema([
                                                Image::make(fn (?EateryReview $record) => $record?->images[$i]->path, '')->imageSize('100%'),
                                            ])
                                            ->modalSubmitAction(false)
                                            ->modalCancelAction(false),
                                    );
                            }

                            return $columns;
                        }),
                ])->visible(fn (EateryReview $record) => $record->images_count > 0),
            ])
            ->contentGrid(['md' => 1])
            ->filters([
                TernaryFilter::make('approved'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('Approve')
                    ->disabled(fn (EateryReview $record) => $record->approved)
                    ->requiresConfirmation()
                    ->icon(Heroicon::OutlinedCheckCircle)
                    ->action(function (EateryReview $record): void {
                        $record->update(['approved' => true]);

                        $record->eatery->sealiacOverview?->update([
                            'invalidated' => true,
                        ]);

                        $record->eatery->touch();

                        (new AnonymousNotifiable())
                            ->route('mail', $record->email)
                            ->notify(new EateryReviewApprovedNotification($record));
                    }),

                DeleteAction::make(),
            ]);
    }
}
