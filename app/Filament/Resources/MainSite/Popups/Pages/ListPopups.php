<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Popups\Pages;

use App\Filament\Resources\MainSite\Popups\PopupResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPopups extends ListRecords
{
    protected static string $resource = PopupResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
