<?php

declare(strict_types=1);

namespace App\Filament\Resources\MainSite\Popups\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PopupsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->latest())
            ->columns([
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
