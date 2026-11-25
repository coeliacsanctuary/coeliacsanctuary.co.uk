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

    public function table(Table $table): Table
    {
        /** @var EateryCounty $county */
        $county = $this->getOwnerRecord();

        $isLondon = $county->slug === 'london';

        $table->getColumn('town')?->label($isLondon ? 'Borough' : 'Town');

        return $table
            ->modelLabel($isLondon ? 'borough' : 'town')
            ->pluralModelLabel($isLondon ? 'boroughs' : 'towns')
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
    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return $ownerRecord->slug === 'london' ? 'Boroughs' : 'Towns';
    }
}
