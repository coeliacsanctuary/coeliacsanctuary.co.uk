<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryAreas\Pages;

use App\Filament\Resources\EatingOut\EateryAreas\EateryAreaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditEateryArea extends EditRecord
{
    protected static string $resource = EateryAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
