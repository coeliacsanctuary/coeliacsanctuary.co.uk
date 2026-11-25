<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryTowns\Tables;

use App\Builders\EatingOut\EateryBuilder;
use App\Builders\EatingOut\NationwideBranchBuilder;
use App\Filament\Resources\EatingOut\EateryBoroughs\EateryBoroughResource;
use App\Filament\Resources\EatingOut\EateryTowns\EateryTownResource;
use App\Filament\Resources\EatingOut\EateryTowns\Schemas\EateryTownForm;
use App\Models\EatingOut\EateryTown;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EateryTownsTable
{
    public static function configure(Table $table, bool $isBorough = false): Table
    {
        $resource = $isBorough ? EateryBoroughResource::class : EateryTownResource::class;

        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                /** @var Builder<EateryTown> $query */
                return $query
                    ->where('slug', '!=', 'nationwide')
                    ->with([
                        'county' => fn ($query) => $query->withoutGlobalScopes(),
                        'county.country',
                    ])
                    ->withCount([
                        'eateries' => fn (EateryBuilder $query): EateryBuilder => $query->where('live', true),
                        'nationwideBranches' => fn (NationwideBranchBuilder $query): NationwideBranchBuilder => $query->where('live', true),
                    ]);
            })
            ->defaultSort('town')
            ->recordUrl(fn (EateryTown $record): string => $resource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('town')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),

                ...$isBorough ? [] : [
                    TextColumn::make('county.county')
                        ->label('County')
                        ->formatStateUsing(fn (EateryTown $record): string => "{$record->county?->country?->country} - {$record->county?->county}"),
                ],

                TextColumn::make('latlng')->label('Lat / Lng'),

                SpatieMediaLibraryImageColumn::make('image')->collection('primary'),

                IconColumn::make('live')
                    ->getStateUsing(fn (EateryTown $record): bool => $record->eateries_count > 0 || $record->nationwide_branches_count > 0)
                    ->boolean(),

                TextColumn::make('eateries_count')
                    ->label('Eateries')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters($isBorough ? [] : [
                SelectFilter::make('county_id')
                    ->label('County')
                    ->options(EateryTownForm::countyOptions(...))
                    ->searchable(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
