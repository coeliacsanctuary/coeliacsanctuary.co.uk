<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryBoroughs\RelationManagers;

use App\Filament\Resources\EatingOut\EateryAreas\EateryAreaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AreasRelationManager extends RelationManager
{
    protected static string $relationship = 'areas';

    protected static ?string $relatedResource = EateryAreaResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->withoutGlobalScopes())
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
