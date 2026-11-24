<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\Eateries\RelationManagers;

use App\Filament\Resources\EatingOut\EaterySuggestedEdits\Tables\EaterySuggestedEditsTable;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class SuggestedEditsRelationManager extends RelationManager
{
    protected static string $relationship = 'suggestedEdits';

    protected static ?string $title = 'Suggested Edits';

    public function table(Table $table): Table
    {
        return EaterySuggestedEditsTable::configure($table);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
