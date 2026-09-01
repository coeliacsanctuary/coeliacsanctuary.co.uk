<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Popups\Tables;

use App\Filament\Resources\MainSite\Popups\PopupResource;
use App\Models\Popup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PopupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('id', 'desc')
            ->recordUrl(fn (Popup $record) => PopupResource::getUrl('edit', ['record' => $record]))
            ->columns([
                TextColumn::make('id')->label('ID'),

                TextColumn::make('text'),

                TextColumn::make('link'),

                TextColumn::make('display_every')->numeric(),

                IconColumn::make('live')->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
