<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryTowns\RelationManagers;

use App\Filament\Resources\EatingOut\NationwideBranches\NationwideBranchResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class NationwideBranchesRelationManager extends RelationManager
{
    protected static string $relationship = 'nationwideBranches';

    protected static ?string $relatedResource = NationwideBranchResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
