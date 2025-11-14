<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Redirects\Pages;

use App\Filament\Resources\MainSite\Redirects\RedirectResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditRedirect extends EditRecord
{
    protected static string $resource = RedirectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
