<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryCounties\RelationManagers;

use App\Filament\Resources\EatingOut\EateryTowns\EateryTownResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class TownsRelationManager extends RelationManager
{
    protected static string $relationship = 'towns';

    protected static ?string $relatedResource = EateryTownResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
