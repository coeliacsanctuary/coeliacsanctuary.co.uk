<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\Eateries\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EateriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->selectRaw('(select country from wheretoeat_countries where wheretoeat_countries.id = wheretoeat.country_id) as order_country')
                ->selectRaw('(select county from wheretoeat_counties where wheretoeat_counties.id = wheretoeat.county_id) as order_county')
                ->selectRaw('(select town from wheretoeat_towns where wheretoeat_towns.id = wheretoeat.town_id) as order_town')
                ->reorder('order_country')
                ->orderBy('order_county')
                ->orderBy('order_town')
            )
            ->columns([
                TextColumn::make('name'),

                TextColumn::make('full_location'),

                TextColumn::make('reviews_count')->label('Reviews')->numeric(),

                TextColumn::make('type.name'),

                IconColumn::make('live')->boolean(),

                IconColumn::make('closed_down')->boolean(),

                TextColumn::make('average_rating')->label('Average Rating')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
