<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryReports\Pages;

use App\Filament\Resources\EatingOut\EateryReports\EateryReportResource;
use Filament\Resources\Pages\ListRecords;

class ListEateryReports extends ListRecords
{
    protected static string $resource = EateryReportResource::class;
}
