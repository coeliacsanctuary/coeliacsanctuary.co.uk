<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\NationwideBranches\Pages;

use App\Filament\Resources\EatingOut\NationwideBranches\NationwideBranchResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNationwideBranches extends ListRecords
{
    protected static string $resource = NationwideBranchResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
