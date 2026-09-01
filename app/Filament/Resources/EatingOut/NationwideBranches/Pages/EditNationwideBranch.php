<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\NationwideBranches\Pages;

use App\Filament\Resources\EatingOut\NationwideBranches\NationwideBranchResource;
use App\Filament\Resources\Pages\BaseEditRecord;
use App\Filament\Support\ProcessEateryLocationData;

class EditNationwideBranch extends BaseEditRecord
{
    protected static string $resource = NationwideBranchResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return ProcessEateryLocationData::handle($data);
    }
}
