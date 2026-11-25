<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryTowns\RelationManagers;

use App\Filament\Resources\EatingOut\Eateries\EateryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EateriesRelationManager extends RelationManager
{
    protected static string $relationship = 'eateries';

    protected static ?string $relatedResource = EateryResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => EateryResource::withLocationAndCounts($query->withoutGlobalScopes()))
            ->headerActions([
                CreateAction::make()
                    ->url(fn (): string => EateryResource::getUrl('create', ['town' => $this->getOwnerRecord()->getKey()])),
            ]);
    }
}
