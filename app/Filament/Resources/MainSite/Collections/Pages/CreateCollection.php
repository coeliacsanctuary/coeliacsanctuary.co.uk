<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Collections\Pages;

use App\Filament\Resources\MainSite\Collections\CollectionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCollection extends CreateRecord
{
    protected static string $resource = CollectionResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return CollectionResource::mutateForSave($data);
    }
}
