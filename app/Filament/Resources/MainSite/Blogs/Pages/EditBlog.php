<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Blogs\Pages;

use App\Filament\Resources\Concerns\SwapsBodyImageFileNamesToUrls;
use App\Filament\Resources\MainSite\Blogs\BlogResource;
use Filament\Resources\Pages\EditRecord;

class EditBlog extends EditRecord
{
    use SwapsBodyImageFileNamesToUrls;

    protected static string $resource = BlogResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return BlogResource::mutateForSave($data);
    }

    protected function afterSave(): void
    {
        $this->swapBodyImageFileNamesToUrls($this->record);
    }
}
