<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryTowns\Tables;

use App\Models\EatingOut\EateryTown;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EateryTownsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn ($query) => $query
                    ->where('slug', '!=', 'nationwide')
                    ->withCount(['areas', 'eateries', 'nationwideBranches'])
                    ->orderBy('town')
            )
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')->collection('primary'),

                TextColumn::make('town')->searchable(),

                TextColumn::make('county.county')->searchable(),

                TextColumn::make('county.country.country')->searchable(),

                TextColumn::make('latlng'),

                IconColumn::make('live')
                    ->getStateUsing(fn (EateryTown $record) => $record->eateries_count > 0 || $record->nationwide_branches_count > 0)
                    ->boolean(),

                TextColumn::make('areas_count')
                    ->label('Areas')
                    ->sortable(),

                TextColumn::make('eateries_count')
                    ->label('Eateries')
                    ->sortable(),

                TextColumn::make('nationwide_branches_count')
                    ->label('Nationwide Branches')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('county')
                    ->relationship('county', 'county')
                    ->searchable(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
