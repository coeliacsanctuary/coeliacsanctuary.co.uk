<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\Eateries\Pages;

use App\Enums\EatingOut\EateryType;
use App\Filament\Resources\EatingOut\Eateries\EateryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListEateries extends ListRecords
{
    protected static string $resource = EateryResource::class;

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(),
            'eateries' => Tab::make()->modifyQueryUsing(
                fn ($query) => $query
                    ->where('type_id', EateryType::EATERY)
                    ->where('county_id', '>', 1)
                    ->when(
                        request()->missing('sort'),
                        fn (Builder $query) => $query
                            ->reorder('wheretoeat_id')
                            ->orderBy('order_country')
                            ->orderBy('order_county')
                            ->orderBy('order_town')
                    )
            ),
            'attractions' => Tab::make()->modifyQueryUsing(
                fn ($query) => $query
                    ->where('type_id', EateryType::ATTRACTION)
                    ->where('county_id', '>', 1)
                    ->when(
                        request()->missing('sort'),
                        fn (Builder $query) => $query
                            ->reorder('wheretoeat_id')
                            ->orderBy('order_country')
                            ->orderBy('order_county')
                            ->orderBy('order_town')
                    )
            ),
            'hotels' => Tab::make()->modifyQueryUsing(
                fn ($query) => $query
                    ->where('type_id', EateryType::HOTEL)
                    ->where('county_id', '>', 1)
                    ->when(
                        request()->missing('sort'),
                        fn (Builder $query) => $query
                            ->reorder('wheretoeat_id')
                            ->orderBy('order_country')
                            ->orderBy('order_county')
                            ->orderBy('order_town')
                    )
            ),
            'chains' => Tab::make()->modifyQueryUsing(
                fn ($query) => $query
                    ->where('county_id', 1)
                    ->when(request()->missing('sort'), fn (Builder $query) => $query->reorder('name'))
            ),
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
