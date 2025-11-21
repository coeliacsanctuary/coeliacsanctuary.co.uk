<?php

declare(strict_types=1);

namespace App\Filament\Fields;

use Filament\Forms\Components\Select;

class LocationLookup extends Select
{
    public function getValidationRules(): array
    {
        return [];
    }
}
