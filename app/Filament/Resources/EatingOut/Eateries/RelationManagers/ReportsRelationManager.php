<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\Eateries\RelationManagers;

use App\Filament\Resources\EatingOut\EateryReports\EateryReportResource;
use Filament\Resources\RelationManagers\RelationManager;

class ReportsRelationManager extends RelationManager
{
    protected static string $relationship = 'reports';

    protected static ?string $relatedResource = EateryReportResource::class;

    protected static ?string $title = 'Reports';

    public function isReadOnly(): bool
    {
        return false;
    }
}
