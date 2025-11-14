<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Announcements\Tables;

use App\Models\Announcement;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class AnnouncementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->latest())
            ->columns([
                TextColumn::make('title')->searchable(),

                ToggleColumn::make('live'),

                IconColumn::make('expired')->getStateUsing(fn (Announcement $record) => $record->expires_at->isPast())->boolean(),

                TextColumn::make('expires_at')->dateTime(),
            ])
            ->recordActions([
                EditAction::make(),

                DeleteAction::make(),
            ]);
    }
}
