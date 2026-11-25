<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EateryCounties\RelationManagers;

use App\Filament\Resources\EatingOut\EateryBoroughs\EateryBoroughResource;
use App\Models\EatingOut\EateryCounty;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BoroughsRelationManager extends RelationManager
{
    protected static string $relationship = 'towns';

    protected static ?string $relatedResource = EateryBoroughResource::class;

    protected static ?string $title = 'Boroughs';

    public function table(Table $table): Table
    {
        $table->getColumn('town')?->label('Borough');

        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withoutGlobalScopes())
            ->headerActions([
                CreateAction::make(),
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    /** @param EateryCounty $ownerRecord */
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->slug === 'london';
    }
}
