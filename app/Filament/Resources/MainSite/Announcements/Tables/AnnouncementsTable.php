<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Announcements\Tables;

use App\Filament\Resources\MainSite\Announcements\AnnouncementResource;
use App\Models\Announcement;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AnnouncementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->recordUrl(fn (Announcement $record) => AnnouncementResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('id')->label('ID')->searchable(),

                TextColumn::make('title')->searchable(['title', 'text']),

                IconColumn::make('live')->boolean(),

                IconColumn::make('expired')
                    ->state(fn (Announcement $record): bool => $record->expires_at->isPast())
                    ->boolean(),

                TextColumn::make('expires_at')->dateTime(),
            ])
            ->filters([
                TernaryFilter::make('live'),

                TernaryFilter::make('expired')->queries(
                    true: fn (Builder $query) => $query->where('expires_at', '<=', now()),
                    false: fn (Builder $query) => $query->where('expires_at', '>', now()),
                ),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
