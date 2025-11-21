<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\Eateries\RelationManagers;

use App\Filament\Resources\EatingOut\NationwideBranches\Schemas\NationwideBranchForm;
use App\Filament\Resources\EatingOut\NationwideBranches\Tables\NationwideBranchesTable;
use App\Models\EatingOut\Eatery;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class BranchesRelationManager extends RelationManager
{
    protected static string $relationship = 'nationwideBranches';

    public function table(Table $table): Table
    {
        return NationwideBranchesTable::configure($table, true)
            ->headerActions([
                CreateAction::make()->modalWidth(Width::SevenExtraLarge),
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return NationwideBranchForm::configure($schema, $this->ownerRecord);
    }

    /** @param Eatery $ownerRecord */
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->country_id === 1;
    }
}
