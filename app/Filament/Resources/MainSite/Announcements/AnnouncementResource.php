<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Announcements;

use App\Filament\Resources\BaseResource;
use App\Filament\Resources\MainSite\Announcements\Pages\CreateAnnouncement;
use App\Filament\Resources\MainSite\Announcements\Pages\EditAnnouncement;
use App\Filament\Resources\MainSite\Announcements\Pages\ListAnnouncements;
use App\Filament\Resources\MainSite\Announcements\Schemas\AnnouncementForm;
use App\Filament\Resources\MainSite\Announcements\Tables\AnnouncementsTable;
use App\Models\Announcement;
use BackedEnum;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class AnnouncementResource extends BaseResource
{
    protected static ?string $model = Announcement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return AnnouncementForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AnnouncementsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAnnouncements::route('/'),
            'create' => CreateAnnouncement::route('/create'),
            'edit' => EditAnnouncement::route('/{record}/edit'),
        ];
    }
}
