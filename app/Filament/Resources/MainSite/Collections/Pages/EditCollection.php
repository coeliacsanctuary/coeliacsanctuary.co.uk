<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Collections\Pages;

use App\Filament\Resources\MainSite\Collections\CollectionResource;
use Filament\Resources\Pages\EditRecord;

class EditCollection extends EditRecord
{
    protected static string $resource = CollectionResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return CollectionResource::mutateForSave($data);
    }
}
