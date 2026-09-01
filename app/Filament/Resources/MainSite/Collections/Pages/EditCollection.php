<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Collections\Pages;

use App\Filament\Resources\MainSite\Collections\CollectionResource;
use App\Filament\Resources\Pages\BaseEditRecord;

class EditCollection extends BaseEditRecord
{
    protected static string $resource = CollectionResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return CollectionResource::mutateForSave($data);
    }
}
