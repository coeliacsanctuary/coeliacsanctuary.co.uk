<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Announcements\Pages;

use App\Filament\Resources\MainSite\Announcements\AnnouncementResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAnnouncement extends CreateRecord
{
    protected static string $resource = AnnouncementResource::class;
}
