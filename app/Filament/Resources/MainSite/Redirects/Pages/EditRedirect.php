<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Redirects\Pages;

use App\Filament\Resources\MainSite\Redirects\RedirectResource;
use App\Filament\Resources\Pages\BaseEditRecord;
use Filament\Actions\DeleteAction;

class EditRedirect extends BaseEditRecord
{
    protected static string $resource = RedirectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
