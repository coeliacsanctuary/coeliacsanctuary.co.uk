<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryMagicRoutes\Pages;

use App\Filament\Resources\EatingOut\EateryMagicRoutes\Actions\RegenerateMagicRouteBodyAction;
use App\Filament\Resources\EatingOut\EateryMagicRoutes\EateryMagicRouteResource;
use Filament\Resources\Pages\ViewRecord;

class ViewEateryMagicRoute extends ViewRecord
{
    protected static string $resource = EateryMagicRouteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            RegenerateMagicRouteBodyAction::make(),
        ];
    }
}
