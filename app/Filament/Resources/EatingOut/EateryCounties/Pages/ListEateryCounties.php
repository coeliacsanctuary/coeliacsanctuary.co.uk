<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryCounties\Pages;

use App\Filament\Resources\EatingOut\EateryCounties\EateryCountyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEateryCounties extends ListRecords
{
    protected static string $resource = EateryCountyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
