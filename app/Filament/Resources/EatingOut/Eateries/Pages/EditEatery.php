<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\Eateries\Pages;

use App\Filament\Resources\EatingOut\Eateries\EateryResource;
use App\Filament\Resources\Pages\BaseEditRecord;
use App\Filament\Support\ProcessEateryLocationData;

class EditEatery extends BaseEditRecord
{
    protected static string $resource = EateryResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return ProcessEateryLocationData::handle($data);
    }
}
