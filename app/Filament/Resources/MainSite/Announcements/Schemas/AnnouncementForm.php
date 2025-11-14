<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Announcements\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AnnouncementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('title')->required(),

                        Textarea::make('text')->rows(5)->required(),

                        Toggle::make('live'),

                        DateTimePicker::make('expires_at')->required()->default(now()->addWeek()),
                    ]),
            ]);
    }
}
