<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\Eateries\Pages;

use App\Filament\Resources\EatingOut\Eateries\EateryResource;
use App\Filament\Support\ProcessEateryLocationData;
use Filament\Resources\Pages\CreateRecord;

class CreateEatery extends CreateRecord
{
    protected static string $resource = EateryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return ProcessEateryLocationData::handle($data);
    }
}
