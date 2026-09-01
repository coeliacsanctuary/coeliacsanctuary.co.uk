<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Announcements\Pages;

use App\Filament\Resources\MainSite\Announcements\AnnouncementResource;
use App\Filament\Resources\Pages\BaseEditRecord;
use Filament\Actions\DeleteAction;

class EditAnnouncement extends BaseEditRecord
{
    protected static string $resource = AnnouncementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
