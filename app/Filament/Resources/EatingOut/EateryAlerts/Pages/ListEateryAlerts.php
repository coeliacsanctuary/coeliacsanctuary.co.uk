<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryAlerts\Pages;

use App\Filament\Resources\EatingOut\EateryAlerts\EateryAlertResource;
use Filament\Resources\Pages\ListRecords;

class ListEateryAlerts extends ListRecords
{
    protected static string $resource = EateryAlertResource::class;
}
