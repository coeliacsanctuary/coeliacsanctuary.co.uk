<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\Eateries\Pages;

use App\Filament\Resources\EatingOut\Eateries\EateryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEatery extends ViewRecord
{
    protected static string $resource = EateryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
