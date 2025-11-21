<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\NationwideBranches\Schemas;

use App\Filament\Shared\SchemaPartials\EateryIntroductionSection;
use App\Filament\Shared\SchemaPartials\EateryLocationSection;
use App\Models\EatingOut\Eatery;
use Filament\Schemas\Schema;

class NationwideBranchForm
{
    public static function configure(Schema $schema, ?Eatery $fromChain = null): Schema
    {
        return $schema
            ->components([
                EateryIntroductionSection::make(isBranch: true),

                EateryLocationSection::make(isBranch: true, fromChain: $fromChain),
            ]);
    }
}
