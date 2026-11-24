<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\Eateries\Schemas;

use App\Enums\EatingOut\EateryType;
use App\Models\EatingOut\Eatery;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class EateryInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Grid::make()
                ->columns(5)
                ->columnSpanFull()
                ->schema([
                    Section::make('Introduction')
                        ->columnSpan(4)
                        ->columns(3)
                        ->schema([
                            TextEntry::make('name'),

                            TextEntry::make('created_at')->dateTime(),

                            TextEntry::make('updated_at')
                                ->label('Last Updated')
                                ->dateTime(),
                        ]),

                    Section::make('Visibility')
                        ->columnSpan(1)
                        ->schema([
                            IconEntry::make('live')->boolean(),

                            IconEntry::make('closed_down')->boolean(),
                        ]),
                ]),

            Section::make('Location')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    TextEntry::make('full_location')->label('Location'),

                    TextEntry::make('address')
                        ->formatStateUsing(fn (string $state): HtmlString => new HtmlString(nl2br(e($state))))
                        ->placeholder('-'),
                ]),

            Section::make('Contact Details')
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    TextEntry::make('phone')
                        ->label('Phone Number')
                        ->placeholder('-'),

                    TextEntry::make('website')
                        ->label('Website')
                        ->url(fn (?string $state): ?string => $state, shouldOpenInNewTab: true)
                        ->placeholder('-'),

                    TextEntry::make('gf_menu_link')
                        ->label('GF Menu Link')
                        ->url(fn (?string $state): ?string => $state, shouldOpenInNewTab: true)
                        ->placeholder('-'),

                    TextEntry::make('facebook_url')
                        ->label('Facebook URL')
                        ->url(fn (?string $state): ?string => $state, shouldOpenInNewTab: true)
                        ->placeholder('-'),

                    TextEntry::make('instagram_url')
                        ->label('Instagram URL')
                        ->url(fn (?string $state): ?string => $state, shouldOpenInNewTab: true)
                        ->placeholder('-'),
                ]),

            Section::make('Details')
                ->columnSpanFull()
                ->columns(3)
                ->schema([
                    TextEntry::make('type.name')->label('Type'),

                    TextEntry::make('venueType.venue_type')
                        ->label('Venue Type')
                        ->placeholder('-'),

                    TextEntry::make('cuisine.cuisine')
                        ->label('Cuisine')
                        ->placeholder('-'),

                    TextEntry::make('info')
                        ->columnSpanFull()
                        ->formatStateUsing(fn (string $state): HtmlString => new HtmlString(nl2br(e($state))))
                        ->placeholder('-'),

                    TextEntry::make('snippet')
                        ->columnSpanFull()
                        ->placeholder('-'),

                    RepeatableEntry::make('restaurants')
                        ->columnSpanFull()
                        ->visible(fn (Eatery $record): bool => (int) $record->type_id === EateryType::ATTRACTION->value)
                        ->schema([
                            TextEntry::make('restaurant_name')->placeholder('-'),

                            TextEntry::make('info'),
                        ]),
                ]),
        ]);
    }
}
