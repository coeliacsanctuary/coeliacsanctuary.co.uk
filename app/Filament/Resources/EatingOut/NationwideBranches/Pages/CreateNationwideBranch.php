<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\NationwideBranches\Pages;

use App\Filament\Resources\EatingOut\NationwideBranches\NationwideBranchResource;
use App\Filament\Support\ProcessEateryLocationData;
use Filament\Resources\Pages\CreateRecord;

class CreateNationwideBranch extends CreateRecord
{
    protected static string $resource = NationwideBranchResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return ProcessEateryLocationData::handle($data);
    }
}
