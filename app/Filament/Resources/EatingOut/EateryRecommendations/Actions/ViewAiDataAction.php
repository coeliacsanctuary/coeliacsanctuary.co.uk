<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryRecommendations\Actions;

use App\Models\EatingOut\EateryRecommendation;
use Filament\Actions\Action;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class ViewAiDataAction
{
    public static function make(): Action
    {
        return Action::make('viewAiData')
            ->label('View AI Data')
            ->icon(Heroicon::OutlinedSparkles)
            ->color('gray')
            ->visible(fn (EateryRecommendation $record): bool => $record->aiData !== null)
            ->slideOver()
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close')
            ->schema([
                Section::make('Eligibility')
                    ->schema([
                        TextEntry::make('aiData.is_eligible')
                            ->label('Eligible')
                            ->badge()
                            ->formatStateUsing(fn (?bool $state): string => $state ? 'Eligible' : 'Not eligible')
                            ->color(fn (?bool $state): string => $state ? 'success' : 'danger')
                            ->placeholder('-'),

                        TextEntry::make('aiData.explanation')
                            ->label('Explanation')
                            ->markdown()
                            ->placeholder('-'),
                    ]),

                Section::make('Location')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('aiData.place_name')->label('Name')->placeholder('-'),
                        TextEntry::make('aiData.place_address')->label('Address')->placeholder('-'),
                        TextEntry::make('aiData.place_country')->label('Country')->placeholder('-'),
                        TextEntry::make('aiData.place_county')->label('County')->placeholder('-'),
                        TextEntry::make('aiData.place_town')->label('Town')->placeholder('-'),
                        TextEntry::make('aiData.place_area')->label('Area')->placeholder('-'),
                        TextEntry::make('aiData.latitude')->label('Latitude')->placeholder('-'),
                        TextEntry::make('aiData.longitude')->label('Longitude')->placeholder('-'),
                    ]),

                Section::make('Contact Details')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('aiData.phone_number')->label('Phone Number')->placeholder('-'),
                        TextEntry::make('aiData.website')->label('Website')->url(fn (?string $state): ?string => $state, shouldOpenInNewTab: true)->placeholder('-'),
                        TextEntry::make('aiData.facebook')->label('Facebook')->url(fn (?string $state): ?string => $state, shouldOpenInNewTab: true)->placeholder('-'),
                        TextEntry::make('aiData.instagram')->label('Instagram')->url(fn (?string $state): ?string => $state, shouldOpenInNewTab: true)->placeholder('-'),
                    ]),

                Section::make('Details')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('aiData.eatery_type')->label('Eatery Type')->placeholder('-'),
                        TextEntry::make('aiData.venue_type')->label('Venue Type')->placeholder('-'),
                        TextEntry::make('aiData.cuisine')->label('Cuisine')->placeholder('-'),
                        TextEntry::make('aiData.info')->label('Info')->markdown()->columnSpanFull()->placeholder('-'),
                    ]),

                Section::make('Features')
                    ->schema([
                        TextEntry::make('aiData.features')
                            ->hiddenLabel()
                            ->badge()
                            ->placeholder('-'),
                    ]),
            ]);
    }
}
