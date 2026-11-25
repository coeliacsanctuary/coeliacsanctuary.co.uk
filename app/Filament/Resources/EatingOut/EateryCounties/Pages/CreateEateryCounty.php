<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryCounties\Pages;

use App\Filament\Resources\EatingOut\EateryCounties\EateryCountyResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEateryCounty extends CreateRecord
{
    protected static string $resource = EateryCountyResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return [
            ...$data,
            'legacy' => '',
        ];
    }
}
