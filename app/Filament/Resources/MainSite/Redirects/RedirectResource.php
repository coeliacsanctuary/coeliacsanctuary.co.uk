<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Redirects;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\MainSite\Redirects\Pages\CreateRedirect;
use App\Filament\Resources\MainSite\Redirects\Pages\EditRedirect;
use App\Filament\Resources\MainSite\Redirects\Pages\ListRedirects;
use App\Filament\Resources\MainSite\Redirects\Schemas\RedirectForm;
use App\Filament\Resources\MainSite\Redirects\Tables\RedirectsTable;
use App\Models\Redirect;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class RedirectResource extends BaseResource
{
    protected static ?string $model = Redirect::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowUturnDown;

    public static function form(Schema $schema): Schema
    {
        return RedirectForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RedirectsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRedirects::route('/'),
            'create' => CreateRedirect::route('/create'),
            'edit' => EditRedirect::route('/{record}/edit'),
        ];
    }
}
