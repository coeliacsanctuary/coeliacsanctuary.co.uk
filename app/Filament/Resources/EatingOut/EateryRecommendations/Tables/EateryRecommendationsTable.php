<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryRecommendations\Tables;

use App\Filament\Resources\EatingOut\EateryRecommendations\Actions\CheckWithAiAction;
use App\Filament\Resources\EatingOut\EateryRecommendations\Actions\CompleteRecommendationAction;
use App\Filament\Resources\EatingOut\EateryRecommendations\Actions\ConvertToEateryAction;
use App\Filament\Resources\EatingOut\EateryRecommendations\Actions\IgnoreAndSendAlreadyExistsAction;
use App\Filament\Resources\EatingOut\EateryRecommendations\Actions\IgnoreAndSendSmallBusinessAction;
use App\Filament\Resources\EatingOut\EateryRecommendations\Actions\IgnoreRecommendationAction;
use App\Filament\Resources\EatingOut\EateryRecommendations\Actions\ViewAiDataAction;
use App\Models\EatingOut\EateryRecommendation;
use App\Models\EatingOut\EateryRecommendationAiData;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\IconPosition;
use Filament\Support\Enums\TextSize;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EateryRecommendationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                /** @var Builder<EateryRecommendation> $query */
                return $query
                    ->where('email', '!=', 'alisondwheatley@gmail.com')
                    ->with(['aiData', 'venueType']);
            })
            ->defaultSort(function (Builder $query): Builder {
                /** @var Builder<EateryRecommendation> $query */
                return $query
                    ->orderByRaw('(completed = 1 or ignored = 1) asc')
                    ->orderByDesc('updated_at');
            })
            ->poll(fn (): ?string => EateryRecommendationAiData::query()->whereNull('completed_at')->whereNull('failed_at')->exists() ? '5s' : null)
            ->columns([
                Stack::make([
                    Split::make([
                        TextColumn::make('place_name')
                            ->searchable()
                            ->size(TextSize::Large)
                            ->weight(FontWeight::Bold),

                        TextColumn::make('ai_status')
                            ->state(fn (EateryRecommendation $record): ?string => match (true) {
                                $record->aiData === null => null,
                                $record->aiData->failed_at !== null => 'AI: Failed',
                                $record->aiData->completed_at === null => 'AI: Checking…',
                                $record->aiData->is_eligible => 'AI: Eligible',
                                default => 'AI: Not eligible',
                            })
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'AI: Failed' => 'warning',
                                'AI: Eligible' => 'success',
                                'AI: Not eligible' => 'danger',
                                default => 'gray',
                            })
                            ->alignEnd()
                            ->grow(false),

                        TextColumn::make('status')
                            ->state(fn (EateryRecommendation $record): string => match (true) {
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

                    TextColumn::make('place_location')
                        ->searchable()
                        ->icon(Heroicon::OutlinedMapPin)
                        ->formatStateUsing(fn (string $state): string => str_replace(',', '<br />', e($state)))
                        ->html(),

                    Split::make([
                        TextColumn::make('name')
                            ->icon(Heroicon::OutlinedUser)
                            ->description(fn (EateryRecommendation $record): string => $record->email)
                            ->color('gray')
                            ->size(TextSize::Small)
                            ->grow(false),

                        TextColumn::make('place_web_address')
                            ->icon(Heroicon::OutlinedGlobeAlt)
                            ->url(fn (?string $state): ?string => $state, shouldOpenInNewTab: true)
                            ->color('gray')
                            ->size(TextSize::Small)
                            ->grow(false),

                        TextColumn::make('venueType.venue_type')
                            ->icon(Heroicon::OutlinedBuildingStorefront)
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

                    TextColumn::make('place_details')
                        ->searchable()
                        ->wrap(),
                ])->space(3),
            ])
            ->contentGrid(['md' => 1])
            ->filters([
                TernaryFilter::make('completed'),
                TernaryFilter::make('ignored'),
            ])
            ->recordActions([
                ConvertToEateryAction::make()->button(),

                ActionGroup::make([
                    CheckWithAiAction::make(),

                    ViewAiDataAction::make(),

                    CompleteRecommendationAction::make(),

                    IgnoreAndSendAlreadyExistsAction::make(),

                    IgnoreAndSendSmallBusinessAction::make(),

                    IgnoreRecommendationAction::make(),

                    DeleteAction::make(),
                ])
                    ->label('More')
                    ->icon(Heroicon::OutlinedChevronDown)
                    ->iconPosition(IconPosition::After)
                    ->color('gray')
                    ->button(),
            ]);
    }
}
