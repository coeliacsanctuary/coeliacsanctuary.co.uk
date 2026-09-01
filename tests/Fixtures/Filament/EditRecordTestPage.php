<?php

declare(strict_types=1);

namespace Tests\Fixtures\Filament;

use App\Filament\Resources\MainSite\Redirects\RedirectResource;
use App\Filament\Resources\Pages\BaseEditRecord;

class EditRecordTestPage extends BaseEditRecord
{
    protected static string $resource = RedirectResource::class;
}
