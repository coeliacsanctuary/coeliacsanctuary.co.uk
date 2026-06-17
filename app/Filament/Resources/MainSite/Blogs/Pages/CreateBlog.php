<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Blogs\Pages;

use App\Filament\Resources\Concerns\SwapsBodyImageFileNamesToUrls;
use App\Filament\Resources\MainSite\Blogs\BlogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBlog extends CreateRecord
{
    use SwapsBodyImageFileNamesToUrls;

    protected static string $resource = BlogResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return BlogResource::mutateForSave($data);
    }

    protected function afterCreate(): void
    {
        $this->swapBodyImageFileNamesToUrls($this->record);
    }
}
