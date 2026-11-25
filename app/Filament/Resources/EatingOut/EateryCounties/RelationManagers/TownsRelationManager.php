<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryCounties\RelationManagers;

use App\Filament\Resources\EatingOut\EateryTowns\EateryTownResource;
use App\Models\EatingOut\EateryCounty;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TownsRelationManager extends RelationManager
{
    protected static string $relationship = 'towns';

    protected static ?string $relatedResource = EateryTownResource::class;

    protected static ?string $title = 'Towns';

    public function table(Table $table): Table
    {
        $table->getColumn('town')?->label('Town');

        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes())
            ->headerActions([
                CreateAction::make()
                    ->url(fn (): string => EateryTownResource::getUrl('create', ['county' => $this->getOwnerRecord()->getKey()])),
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    /** @param EateryCounty $ownerRecord */
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->slug !== 'london';
    }
}
