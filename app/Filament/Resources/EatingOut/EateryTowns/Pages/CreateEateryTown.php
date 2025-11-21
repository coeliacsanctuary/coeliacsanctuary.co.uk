<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryTowns\Pages;

use App\Filament\Resources\EatingOut\EateryTowns\EateryTownResource;
use Filament\Resources\Pages\CreateRecord;

class CreateEateryTown extends CreateRecord
{
    protected static string $resource = EateryTownResource::class;
}
