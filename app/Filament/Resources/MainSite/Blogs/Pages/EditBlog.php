<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Blogs\Pages;

use App\Filament\Resources\MainSite\Blogs\BlogResource;
use App\Filament\Resources\Pages\BaseEditRecord;

class EditBlog extends BaseEditRecord
{
    protected static string $resource = BlogResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        return BlogResource::mutateForSave($data);
    }
}
