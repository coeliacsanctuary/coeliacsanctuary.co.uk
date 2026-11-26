<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryMagicRoutes\Tables;

use App\Filament\Resources\EatingOut\EateryMagicRoutes\Actions\RegenerateMagicRouteBodyAction;
use App\Filament\Resources\EatingOut\EateryMagicRoutes\EateryMagicRouteResource;
use App\Models\EatingOut\EateryMagicRouteRecord;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EateryMagicRoutesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id')
            ->recordUrl(fn (EateryMagicRouteRecord $record): string => EateryMagicRouteResource::getUrl('view', ['record' => $record]))
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('resolver_type')
                    ->label('Magic Route Type'),

                TextColumn::make('location')
                    ->state(fn (EateryMagicRouteRecord $record): string => EateryMagicRouteResource::locationLabel($record)),

                TextColumn::make('eateries')
                    ->numeric()
                    ->state(fn (EateryMagicRouteRecord $record): int => EateryMagicRouteResource::eateriesCount($record)),
            ])
            ->recordActions([
                RegenerateMagicRouteBodyAction::make(),
            ]);
    }
}
