<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryCounties\Tables;

use App\Models\EatingOut\EateryCounty;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class EateryCountiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn ($query) => $query
                    ->where('id', '>', 1)
                    ->withCount(['towns', 'eateries', 'nationwideBranches'])
            )
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')->collection('primary'),

                TextColumn::make('county')->searchable(),

                TextColumn::make('country.country')->searchable(),

                TextColumn::make('latlng'),

                IconColumn::make('live')
                    ->getStateUsing(fn (EateryCounty $record) => $record->eateries_count > 0 || $record->nationwide_branches_count > 0)
                    ->boolean(),

                TextColumn::make('towns_count')
                    ->label('Towns')
                    ->sortable(),

                TextColumn::make('eateries_count')
                    ->label('Eateries')
                    ->sortable(),

                TextColumn::make('nationwide_branches_count')
                    ->label('Nationwide Branches')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('country')
                    ->relationship('country', 'country'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
