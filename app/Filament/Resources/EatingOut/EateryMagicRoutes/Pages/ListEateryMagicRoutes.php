<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryMagicRoutes\Pages;

use App\Filament\Resources\EatingOut\EateryMagicRoutes\EateryMagicRouteResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEateryMagicRoutes extends ListRecords
{
    protected static string $resource = EateryMagicRouteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
