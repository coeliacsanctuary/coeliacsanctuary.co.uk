<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryTowns\Pages;

use App\Filament\Resources\EatingOut\EateryTowns\EateryTownResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEateryTowns extends ListRecords
{
    protected static string $resource = EateryTownResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
