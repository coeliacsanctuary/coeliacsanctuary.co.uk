<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryBoroughs\Pages;

use App\Filament\Resources\EatingOut\EateryBoroughs\EateryBoroughResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEateryBorough extends CreateRecord
{
    protected static string $resource = EateryBoroughResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return [
            ...$data,
            'county_id' => EateryBoroughResource::londonId(),
            'legacy' => '',
        ];
    }
}
