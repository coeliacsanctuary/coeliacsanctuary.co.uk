<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryAreas\Tables;

use App\Builders\EatingOut\EateryBuilder;
use App\Builders\EatingOut\NationwideBranchBuilder;
use App\Filament\Resources\EatingOut\EateryAreas\EateryAreaResource;
use App\Filament\Resources\EatingOut\EateryAreas\Schemas\EateryAreaForm;
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
            ->modifyQueryUsing(function (Builder $query): Builder {
                /** @var Builder<EateryArea> $query */
                return $query
                    ->with(['town' => fn ($query) => $query->withoutGlobalScopes()])
                    ->withCount([
                        'eateries' => fn (EateryBuilder $query): EateryBuilder => $query->where('live', true),
                        'nationwideBranches' => fn (NationwideBranchBuilder $query): NationwideBranchBuilder => $query->where('live', true),
                    ]);
            })
            ->defaultSort('area')
            ->recordUrl(fn (EateryArea $record): string => EateryAreaResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('area')
                    ->label('Name')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('town.town')->label('Borough'),

                TextColumn::make('latlng')->label('Lat / Lng'),

                SpatieMediaLibraryImageColumn::make('image')->collection('primary'),

                IconColumn::make('live')
                    ->getStateUsing(fn (EateryArea $record): bool => $record->eateries_count > 0 || $record->nationwide_branches_count > 0)
                    ->boolean(),

                TextColumn::make('eateries_count')
                    ->label('Eateries')
                    ->numeric()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('town_id')
                    ->label('Borough')
                    ->options(EateryAreaForm::boroughOptions(...))
                    ->searchable(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
