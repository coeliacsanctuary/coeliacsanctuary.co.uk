<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryAreas\Tables;

use App\Models\EatingOut\EateryArea;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EateryAreasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn ($query) => $query
                    ->withCount(['eateries', 'nationwideBranches'])
                    ->orderBy('area')
            )
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')->collection('primary'),

                TextColumn::make('area')->searchable(),

                TextColumn::make('town.town')->label('Borough')->searchable(),

                TextColumn::make('latlng'),

                IconColumn::make('live')
                    ->getStateUsing(fn (EateryArea $record) => $record->eateries_count > 0 || $record->nationwide_branches_count > 0)
                    ->boolean(),

                TextColumn::make('eateries_count')
                    ->label('Eateries')
                    ->sortable(),

                TextColumn::make('nationwide_branches_count')
                    ->label('Nationwide Branches')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('borough')
                    ->relationship('town', 'town', fn (Builder $query) => $query->whereRelation('county', 'slug', 'london'))
                    ->searchable(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
