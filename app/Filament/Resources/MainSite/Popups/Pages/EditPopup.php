<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Popups\Pages;

use App\Filament\Resources\MainSite\Popups\PopupResource;
use Filament\Resources\Pages\EditRecord;

class EditPopup extends EditRecord
{
    protected static string $resource = PopupResource::class;
}
