<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\Eateries\Tables;

use App\Filament\Resources\EatingOut\Eateries\Actions\GenerateSealiacOverviewAction;
use App\Filament\Resources\EatingOut\Eateries\RelationManagers\BranchesRelationManager;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class NationwideBranchesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn (Builder $query) => $query
                    ->withoutGlobalScopes()
                    ->addSelect('wheretoeat_nationwide_branches.*')
                    ->selectRaw('(select country from wheretoeat_countries where wheretoeat_countries.id = wheretoeat_nationwide_branches.country_id) as order_country')
                    ->selectRaw('(select county from wheretoeat_counties where wheretoeat_counties.id = wheretoeat_nationwide_branches.county_id) as order_county')
                    ->selectRaw('(select town from wheretoeat_towns where wheretoeat_towns.id = wheretoeat_nationwide_branches.town_id) as order_town')
                    ->selectRaw('(select area from wheretoeat_areas where wheretoeat_areas.id = wheretoeat_nationwide_branches.area_id) as order_area')
                    ->with([
                        'eatery' => fn ($query) => $query->withoutGlobalScopes(),
                        'area' => fn ($query) => $query->withoutGlobalScopes(),
                        'town' => fn ($query) => $query->withoutGlobalScopes(),
                        'county' => fn ($query) => $query->withoutGlobalScopes(),
                        'country' => fn ($query) => $query->withoutGlobalScopes(),
                    ])
                    ->withCount([
                        'reviews' => fn ($query) => $query->withoutGlobalScopes(),
                    ])
            )
            ->defaultSort(
                fn (Builder $query): Builder => $query
                    ->orderBy('order_country')
                    ->orderBy('order_county')
                    ->orderBy('order_town')
                    ->orderBy('order_area')
            )
            ->defaultPaginationPageOption(25)
            ->columns([
                TextColumn::make('name')
                    ->sortable()
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->whereLike('wheretoeat_nationwide_branches.name', "%{$search}%")
                        ->orWhere('wheretoeat_nationwide_branches.id', $search)),

                TextColumn::make('full_location')
                    ->searchable(query: fn (Builder $query, string $search): Builder => $query
                        ->whereLike('wheretoeat_nationwide_branches.address', "%{$search}%")
                        ->orWhereHas('town', fn (Builder $town) => $town->withoutGlobalScopes()->whereLike('town', "%{$search}%"))
                        ->orWhereHas('county', fn (Builder $county) => $county->withoutGlobalScopes()->whereLike('county', "%{$search}%"))
                        ->orWhereHas('area', fn (Builder $area) => $area->withoutGlobalScopes()->whereLike('area', "%{$search}%"))),

                TextColumn::make('reviews_count')
                    ->label('Reviews')
                    ->numeric(),

                IconColumn::make('live')->boolean(),
            ])
            ->filters([
                TernaryFilter::make('live'),

                TernaryFilter::make('reviewed')
                    ->queries(
                        true: fn (Builder $query): Builder => $query->whereHas('reviews', fn (Builder $reviews) => $reviews->withoutGlobalScopes()),
                        false: fn (Builder $query): Builder => $query->whereDoesntHave('reviews', fn (Builder $reviews) => $reviews->withoutGlobalScopes()),
                        blank: fn (Builder $query): Builder => $query,
                    ),
            ])
            ->recordActions([
                GenerateSealiacOverviewAction::make(),

                ViewAction::make()->modalWidth(Width::SevenExtraLarge),

                EditAction::make()
                    ->modalWidth(Width::SevenExtraLarge)
                    ->after(fn (BranchesRelationManager $livewire) => $livewire->getOwnerRecord()->touch()),
            ]);
    }
}
