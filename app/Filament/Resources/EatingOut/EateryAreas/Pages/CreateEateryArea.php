<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryAreas\Pages;

use App\Filament\Resources\EatingOut\EateryAreas\EateryAreaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEateryArea extends CreateRecord
{
    protected static string $resource = EateryAreaResource::class;
}
