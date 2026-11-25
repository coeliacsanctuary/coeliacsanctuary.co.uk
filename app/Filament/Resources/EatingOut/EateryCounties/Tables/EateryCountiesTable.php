<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryCounties\Tables;

use App\Builders\EatingOut\EateryBuilder;
use App\Builders\EatingOut\NationwideBranchBuilder;
use App\Filament\Resources\EatingOut\EateryCounties\EateryCountyResource;
use App\Models\EatingOut\EateryCounty;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EateryCountiesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query): Builder => $query
                    ->where('slug', '!=', 'nationwide')
                    ->with(['country'])
                    ->withCount([
                        'towns',
                        'eateries' => fn (EateryBuilder $query): EateryBuilder => $query->where('live', true),
                        'nationwideBranches' => fn (NationwideBranchBuilder $query): NationwideBranchBuilder => $query->where('live', true),
                    ])
            )
            ->defaultSort('county')
            ->recordUrl(fn (EateryCounty $record): string => EateryCountyResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('county')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('country.country')->label('Country'),

                TextColumn::make('latlng')->label('Lat / Lng'),

                SpatieMediaLibraryImageColumn::make('image')->collection('primary'),

                IconColumn::make('live')
                    ->getStateUsing(fn (EateryCounty $record): bool => $record->eateries_count > 0 || $record->nationwide_branches_count > 0)
                    ->boolean(),

                TextColumn::make('towns_count')
                    ->label('Towns')
                    ->numeric()
                    ->sortable(),

                TextColumn::make('eateries_count')
                    ->label('Eateries')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('country')
                    ->relationship('country', 'country'),
            ])
            ->recordActions([
                ViewAction::make(),

                EditAction::make(),
            ]);
    }
}
