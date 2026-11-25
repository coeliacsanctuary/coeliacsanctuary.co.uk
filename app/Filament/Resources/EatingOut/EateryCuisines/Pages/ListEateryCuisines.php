<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryCuisines\Pages;

use App\Filament\Resources\EatingOut\EateryCuisines\EateryCuisineResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEateryCuisines extends ListRecords
{
    protected static string $resource = EateryCuisineResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
