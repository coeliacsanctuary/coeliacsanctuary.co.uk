<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\Eateries\RelationManagers;

use App\Filament\Resources\MainSite\SealiacOverviews\SealiacOverviewResource;
use Filament\Resources\RelationManagers\RelationManager;

class SealiacOverviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'sealiacOverviews';

    protected static ?string $relatedResource = SealiacOverviewResource::class;

    protected static ?string $title = 'Sealiac Overviews';

    public function isReadOnly(): bool
    {
        return false;
    }
}
