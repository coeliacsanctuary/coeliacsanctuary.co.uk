<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Popups;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\MainSite\Popups\Pages\CreatePopup;
use App\Filament\Resources\MainSite\Popups\Pages\EditPopup;
use App\Filament\Resources\MainSite\Popups\Pages\ListPopups;
use App\Filament\Resources\MainSite\Popups\Schemas\PopupForm;
use App\Filament\Resources\MainSite\Popups\Tables\PopupsTable;
use App\Models\Popup;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PopupResource extends BaseResource
{
    protected static ?string $model = Popup::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    public static function form(Schema $schema): Schema
    {
        return PopupForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PopupsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPopups::route('/'),
            'create' => CreatePopup::route('/create'),
            'edit' => EditPopup::route('/{record}/edit'),
        ];
    }
}
