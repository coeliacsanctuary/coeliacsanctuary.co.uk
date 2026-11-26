<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryMagicRoutes\Schemas;

use App\Filament\Resources\EatingOut\EateryMagicRoutes\EateryMagicRouteResource;
use App\Models\EatingOut\EateryMagicRouteRecord;
use Filament\Infolists\Components\CodeEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EateryMagicRouteInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make()
                ->columnSpanFull()
                ->columns(2)
                ->schema([
                    TextEntry::make('id')->label('ID'),

                    TextEntry::make('resolver_type')->label('Magic Route Type'),

                    TextEntry::make('location')
                        ->state(fn (EateryMagicRouteRecord $record): string => EateryMagicRouteResource::locationLabel($record)),

                    TextEntry::make('eateries')
                        ->numeric()
                        ->state(fn (EateryMagicRouteRecord $record): int => EateryMagicRouteResource::eateriesCount($record)),
                ]),

            KeyValueEntry::make('body')
                ->label('Content')
                ->columnSpanFull()
                ->state(fn (EateryMagicRouteRecord $record): array => collect($record->body)
                    ->map(fn (mixed $value): mixed => is_array($value) ? implode(', ', $value) : $value)
                    ->all()),

            CodeEntry::make('builder_config')
                ->label('Configuration')
                ->columnSpanFull()
                ->state(fn (EateryMagicRouteRecord $record): array => json_decode($record->getRawOriginal('builder_config'), true)),
        ]);
    }
}
