<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\Eateries\Tables;

use App\Filament\Resources\EatingOut\Eateries\Actions\GenerateSealiacOverviewAction;
use App\Filament\Resources\EatingOut\Eateries\EateryResource;
use App\Models\EatingOut\Eatery;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ReplicateAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EateriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query) => $query
                    ->addSelect('wheretoeat.*')
                    ->selectRaw('(select country from wheretoeat_countries where wheretoeat_countries.id = wheretoeat.country_id) as order_country')
                    ->selectRaw('(select county from wheretoeat_counties where wheretoeat_counties.id = wheretoeat.county_id) as order_county')
                    ->selectRaw('(select town from wheretoeat_towns where wheretoeat_towns.id = wheretoeat.town_id) as order_town')
                    ->when(
                        request()->missing('sort'),
                        fn (Builder $query) => $query
                            ->reorder('order_country')
                            ->orderBy('order_county')
                            ->orderBy('order_town')
                    )
            )
            ->columns([
                TextColumn::make('name')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->whereLike('wheretoeat.name', "%{$search}%")
                        ->orWhere('wheretoeat.id', $search)),

                TextColumn::make('full_location')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->whereHas('town', fn (Builder $town) => $town->withoutGlobalScopes()->whereLike('town', "%{$search}%"))
                        ->orWhereHas('county', fn (Builder $county) => $county->withoutGlobalScopes()->whereLike('county', "%{$search}%"))
                        ->orWhereHas('area', fn (Builder $area) => $area->withoutGlobalScopes()->whereLike('area', "%{$search}%"))),

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
            ])
            ->filters([
                TernaryFilter::make('live'),

                TernaryFilter::make('closed_down'),

                SelectFilter::make('type_id')
                    ->label('Type')
                    ->relationship('type', 'name'),

                TernaryFilter::make('reviewed')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereHas('reviews', fn (Builder $reviews) => $reviews->withoutGlobalScopes()),
                        false: fn (Builder $query): Builder => $query->whereDoesntHave('reviews', fn (Builder $reviews) => $reviews->withoutGlobalScopes()),
                        blank: fn (Builder $query): Builder => $query,
                    ),
            ])
            ->recordActions([
                GenerateSealiacOverviewAction::make(),

                ReplicateAction::make()
                    ->excludeAttributes(['slug', 'order_country', 'order_county', 'order_town', 'reviews_count', 'nationwide_branches_count'])
                    ->beforeReplicaSaved(fn (Eatery $replica) => $replica->live = false)
                    ->successRedirectUrl(fn (Eatery $replica) => EateryResource::getUrl('edit', ['record' => $replica])),

                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
