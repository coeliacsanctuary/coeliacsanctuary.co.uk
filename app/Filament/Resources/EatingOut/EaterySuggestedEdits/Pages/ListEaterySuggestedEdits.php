<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EaterySuggestedEdits\Pages;

use App\Filament\Resources\EatingOut\EaterySuggestedEdits\EaterySuggestedEditResource;
use Filament\Resources\Pages\ListRecords;

class ListEaterySuggestedEdits extends ListRecords
{
    protected static string $resource = EaterySuggestedEditResource::class;
}
