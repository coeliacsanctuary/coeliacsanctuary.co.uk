<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\Eateries\RelationManagers;

use App\Filament\Resources\EatingOut\Eateries\Schemas\NationwideBranchForm;
use App\Filament\Resources\EatingOut\Eateries\Schemas\NationwideBranchInfolist;
use App\Filament\Resources\EatingOut\Eateries\Tables\NationwideBranchesTable;
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

    protected static ?string $title = 'Branches';

    protected static ?string $modelLabel = 'branch';

    public function table(Table $table): Table
    {
        return NationwideBranchesTable::configure($table)
            ->headerActions([
                CreateAction::make()
                    ->modalWidth(Width::SevenExtraLarge)
                    ->after(fn () => $this->getOwnerRecord()->touch()),
            ]);
    }

    public function form(Schema $schema): Schema
    {
        return NationwideBranchForm::configure($schema);
    }

    public function infolist(Schema $schema): Schema
    {
        return NationwideBranchInfolist::configure($schema);
    }

    public function isReadOnly(): bool
    {
        return false;
    }

    /** @param Eatery $ownerRecord */
    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return $ownerRecord->county_id === 1;
    }
}
