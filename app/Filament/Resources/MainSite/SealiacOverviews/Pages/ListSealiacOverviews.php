<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\SealiacOverviews\Pages;

use App\Filament\Resources\MainSite\SealiacOverviews\SealiacOverviewResource;
use Filament\Resources\Pages\ListRecords;

class ListSealiacOverviews extends ListRecords
{
    protected static string $resource = SealiacOverviewResource::class;
}
