<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryBoroughs\Pages;

use App\Filament\Resources\EatingOut\EateryBoroughs\EateryBoroughResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEateryBoroughs extends ListRecords
{
    protected static string $resource = EateryBoroughResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
