<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryAreas\RelationManagers;

use App\Filament\Resources\EatingOut\Eateries\EateryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class EateriesRelationManager extends RelationManager
{
    protected static string $relationship = 'eateries';

    protected static ?string $relatedResource = EateryResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
