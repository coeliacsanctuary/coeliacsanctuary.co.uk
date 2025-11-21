<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\Eateries\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EateriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->withoutGlobalScopes()
                ->select('*')
                ->selectRaw('(select country from wheretoeat_countries where wheretoeat_countries.id = wheretoeat.country_id) as order_country')
                ->selectRaw('(select county from wheretoeat_counties where wheretoeat_counties.id = wheretoeat.county_id) as order_county')
                ->selectRaw('(select town from wheretoeat_towns where wheretoeat_towns.id = wheretoeat.town_id) as order_town')
                ->with([
                    'area' => fn ($query) => $query->withoutGlobalScopes(),
                    'town' => fn ($query) => $query->withoutGlobalScopes(),
                    'county' => fn ($query) => $query->withoutGlobalScopes(),
                    'country' => fn ($query) => $query->withoutGlobalScopes(),
                    'reviews' => fn ($query) => $query->withoutGlobalScopes(),
                ])
                ->withCount([
                    'nationwideBranches' => fn ($query) => $query->withoutGlobalScopes(),
                    'reviews' => fn ($query) => $query->withoutGlobalScopes(),
                ])
                ->when(request()->missing('sort'), fn (Builder $query) => $query
                    ->reorder('order_country')
                    ->orderBy('order_county')
                    ->orderBy('order_town')
                )
            )
            ->columns([
                TextColumn::make('name')->searchable(),

                TextColumn::make('full_location'),

                TextColumn::make('nationwide_branches_count')
                    ->label('Branches')
                    ->sortable()
                    ->visible(fn ($livewire) => $livewire->activeTab === 'chains')
                    ->numeric(),

                TextColumn::make('reviews_count')
                    ->label('Reviews')
                    ->sortable()
                    ->numeric(),

                TextColumn::make('type.name'),

                IconColumn::make('live')->boolean(),

                IconColumn::make('closed_down')->boolean(),

                TextColumn::make('average_rating')
                    ->label('Average Rating')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('live'),

                TernaryFilter::make('reviewed')->query(fn (Builder $query): Builder => $query->whereHas('reviews')),
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
