<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\SealiacOverviews;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\MainSite\SealiacOverviews\Pages\ListSealiacOverviews;
use App\Filament\Resources\MainSite\SealiacOverviews\Tables\SealiacOverviewsTable;
use App\Models\SealiacOverview;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class SealiacOverviewResource extends BaseResource
{
    protected static ?string $model = SealiacOverview::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?int $navigationSort = 8;

    protected static string|UnitEnum|null $navigationGroup = 'Main Site';

    public static function table(Table $table): Table
    {
        return SealiacOverviewsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSealiacOverviews::route('/'),
        ];
    }
}
