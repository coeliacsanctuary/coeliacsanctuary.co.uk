<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryCounties\Pages;

use App\Filament\Resources\EatingOut\EateryCounties\EateryCountyResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewEateryCounty extends ViewRecord
{
    protected static string $resource = EateryCountyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
