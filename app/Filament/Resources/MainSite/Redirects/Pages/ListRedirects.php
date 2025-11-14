<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Redirects\Pages;

use App\Filament\Resources\MainSite\Redirects\RedirectResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListRedirects extends ListRecords
{
    protected static string $resource = RedirectResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
