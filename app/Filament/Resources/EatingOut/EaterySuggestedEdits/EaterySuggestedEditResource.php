<?php

declare(strict_types=1);

namespace App\Filament\Resources\EatingOut\EaterySuggestedEdits;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\EatingOut\EaterySuggestedEdits\Pages\ListEaterySuggestedEdits;
use App\Filament\Resources\EatingOut\EaterySuggestedEdits\Tables\EaterySuggestedEditsTable;
use App\Models\EatingOut\EaterySuggestedEdit;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class EaterySuggestedEditResource extends BaseResource
{
    protected static ?string $model = EaterySuggestedEdit::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    public static function table(Table $table): Table
    {
        return EaterySuggestedEditsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEaterySuggestedEdits::route('/'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $count = EaterySuggestedEdit::query()->where('accepted', false)->where('rejected', false)->count();

        if ($count > 0) {
            return (string) $count;
        }

        return null;
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }
}
