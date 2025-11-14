<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Redirects\Pages;

use App\Filament\Resources\MainSite\Redirects\RedirectResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRedirect extends CreateRecord
{
    protected static string $resource = RedirectResource::class;
}
