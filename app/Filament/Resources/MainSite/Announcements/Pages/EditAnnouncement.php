<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Announcements\Pages;

use App\Filament\Resources\MainSite\Announcements\AnnouncementResource;
use App\Filament\Resources\Pages\BaseEditRecord;

class EditAnnouncement extends BaseEditRecord
{
    protected static string $resource = AnnouncementResource::class;
}
